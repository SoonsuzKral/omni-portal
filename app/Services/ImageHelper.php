<?php

namespace App\Services;

use App\Models\ContentNode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ImageHelper
{
    const CACHE_TTL = 604800; // 7 days
    const MAX_KEYWORDS = 5;

    protected array $providers = [
        'picsum' => 'https://picsum.photos',
        'unsplash' => 'https://source.unsplash.com',
    ];

    public function processContentNode(ContentNode $content): array
    {
        $existingImage = $content->featured_image;

        if (!empty($existingImage)) {
            return [
                'url' => $existingImage,
                'alt' => $this->generateAltTag($content),
                'title' => $this->generateTitleTag($content),
                'source' => 'database',
            ];
        }

        $imageData = $this->generateFromContentNode($content);

        if ($imageData['url']) {
            $content->update(['featured_image' => $imageData['url']]);
        }

        return $imageData;
    }

    public function generateFromContentNode(ContentNode $content): array
    {
        $keywords = $this->extractKeywordsFromContent($content);

        $imageUrl = $this->fetchRelevantImage($keywords);

        if (!$imageUrl) {
            $imageUrl = $this->generateDynamicPlaceholder($keywords);
        }

        return [
            'url' => $imageUrl,
            'alt' => $this->generateAltTag($content),
            'title' => $this->generateTitleTag($content),
            'keywords' => $keywords,
            'source' => $imageUrl ? 'auto_generated' : 'placeholder',
        ];
    }

    protected function extractKeywordsFromContent(ContentNode $content): array
    {
        $keywords = [];

        if ($content->seo_title) {
            $titleWords = Str::of($content->seo_title)
                ->explode(' ')
                ->map(fn($w) => Str::lower(trim($w)))
                ->filter(fn($w) => strlen($w) > 3)
                ->take(self::MAX_KEYWORDS)
                ->toArray();
            $keywords = array_merge($keywords, $titleWords);
        }

        if ($content->taxonomy) {
            $keywords[] = Str::slug($content->taxonomy->name);
        }

        if ($content->location) {
            $keywords[] = Str::slug($content->location->name);
        }

        $stopWords = ['the', 'and', 'for', 'with', 'your', 'you', 'best', 'top', 'how', 'what', 'why', 'guide', 'review', '2024', '2025', '2026'];
        $keywords = array_filter($keywords, fn($k) => !in_array($k, $stopWords));

        return array_unique(array_slice($keywords, 0, self::MAX_KEYWORDS));
    }

    protected function fetchRelevantImage(array $keywords): ?string
    {
        $seed = md5(implode(',', $keywords));
        $url = "https://picsum.photos/seed/{$seed}/1200/630";

        try {
            $request = Http::timeout(5);
            if (app()->environment('local')) {
                $request->withoutVerifying();
            }
            $response = $request->get($url);
            if ($response->successful()) {
                return $url;
            }
        } catch (\Exception $e) {
            Log::debug("Image fetch failed: " . $e->getMessage());
        }

        return $this->generateDynamicPlaceholder($keywords);
    }

    protected function tryUnsplashDirect(array $keywords): ?string
    {
        return null;
    }

    protected function generateDynamicPlaceholder(array $keywords): string
    {
        $text = ucfirst(implode(' ', array_slice($keywords, 0, 2)));
        $colors = [
            ['#4F46E5', '#818CF8'],
            ['#059669', '#34D399'],
            ['#DC2626', '#F87171'],
            ['#7C3AED', '#A78BFA'],
            ['#DB2777', '#F472B6'],
            ['#0891B2', '#22D3EE'],
            ['#EA580C', '#FB923C'],
            ['#4B5563', '#9CA3AF'],
        ];
        $colorPair = $colors[array_rand($colors)];

        return $this->generateSvgPlaceholder($text, $colorPair);
    }

    protected function generateSvgPlaceholder(string $text, array $colors): string
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="800" height="420" viewBox="0 0 800 420">
            <defs>
                <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" style="stop-color:' . $colors[0] . ';stop-opacity:1" />
                    <stop offset="100%" style="stop-color:' . $colors[1] . ';stop-opacity:1" />
                </linearGradient>
            </defs>
            <rect width="800" height="420" fill="url(#grad)"/>
            <circle cx="150" cy="100" r="80" fill="rgba(255,255,255,0.1)"/>
            <circle cx="650" cy="320" r="120" fill="rgba(255,255,255,0.08)"/>
            <circle cx="400" cy="210" r="150" fill="rgba(255,255,255,0.05)"/>
            <text x="400" y="220" font-family="Arial, sans-serif" font-size="42" font-weight="bold" fill="white" text-anchor="middle" dominant-baseline="middle">' . htmlspecialchars($text) . '</text>
            <text x="400" y="380" font-family="Arial, sans-serif" font-size="18" fill="rgba(255,255,255,0.7)" text-anchor="middle">Omvi Portal</text>
        </svg>';

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    public function generateAltTag(ContentNode $content): string
    {
        $parts = [];

        if ($content->seo_title) {
            $parts[] = Str::limit($content->seo_title, 50);
        }

        if ($content->location) {
            $parts[] = $content->location->name;
        }

        if ($content->taxonomy) {
            $parts[] = $content->taxonomy->name;
        }

        return implode(' - ', $parts);
    }

    public function generateTitleTag(ContentNode $content): string
    {
        $location = $content->location?->name ?? 'Global';
        $keyword = $content->taxonomy?->name ?? 'Content';

        return "{$location} {$keyword} - " . config('app.name', 'Omvi Portal');
    }

    public function batchProcess(array $contentIds): int
    {
        $processed = 0;

        ContentNode::whereIn('id', $contentIds)
            ->whereNull('featured_image')
            ->whereNotNull('publish_date')
            ->chunk(100, function ($contents) use (&$processed) {
                foreach ($contents as $content) {
                    $this->processContentNode($content);
                    $processed++;
                }
            });

        return $processed;
    }

    public function getSeoAttributes(ContentNode $content): array
    {
        $image = $content->featured_image ?? $this->generateFromContentNode($content)['url'];

        return [
            'og:image' => $image,
            'og:image:width' => '1200',
            'og:image:height' => '630',
            'og:image:alt' => $this->generateAltTag($content),
            'twitter:image' => $image,
            'twitter:alt' => $this->generateAltTag($content),
        ];
    }
}