<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ImageAutoStub
{
    const CACHE_TTL = 86400; // 24 hours

    // Free image APIs (no API key required for basic usage)
    protected array $providers = [
        'unsplash' => 'https://source.unsplash.com',
        'pexels' => 'https://images.pexels.com',
    ];

    /**
     * Get featured image - from DB or auto-generate.
     */
    public function getFeaturedImage(?string $existingImage, ?string $title, ?string $taxonomyName = null, ?string $locationName = null): ?string
    {
        // Return existing if available
        if (!empty($existingImage)) {
            return $existingImage;
        }

        // Generate from title + context
        return $this->generateFromContext($title, $taxonomyName, $locationName);
    }

    /**
     * Generate placeholder image from context keywords.
     */
    public function generateFromContext(?string $title, ?string $taxonomyName = null, ?string $locationName = null): ?string
    {
        // Extract keywords from title
        $keywords = $this->extractKeywords($title);

        // Add taxonomy and location context
        if ($taxonomyName) {
            $keywords[] = $taxonomyName;
        }
        if ($locationName) {
            $keywords[] = $locationName;
        }

        // Try primary provider (Unsplash)
        $imageUrl = $this->tryUnsplash($keywords);

        // Fallback to Pexels if Unsplash fails
        if (!$imageUrl) {
            $imageUrl = $this->tryPexels($keywords);
        }

        // Ultimate fallback - use a solid color placeholder service
        if (!$imageUrl) {
            $imageUrl = $this->generatePlaceholderUrl($keywords);
        }

        return $imageUrl;
    }

    /**
     * Extract SEO-friendly keywords from title.
     */
    protected function extractKeywords(?string $title): array
    {
        if (empty($title)) {
            return ['default', 'business'];
        }

        // Remove common words
        $stopWords = ['the', 'and', 'for', 'with', 'your', 'you', 'best', 'top', 'how', 'what', 'why', 'guide'];

        $words = Str::of($title)
            ->explode(' ')
            ->map(fn($word) => Str::lower(trim($word)))
            ->filter(fn($word) => strlen($word) > 3)
            ->reject(fn($word) => in_array($word, $stopWords))
            ->take(3)
            ->toArray();

        return !empty($words) ? $words : ['business'];
    }

    /**
     * Try Lorem Picsum (free, no auth needed).
     */
    protected function tryUnsplash(array $keywords): ?string
    {
        $seed = md5(implode(',', $keywords));
        $url = "https://picsum.photos/seed/{$seed}/800/600";

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
            Log::debug("Picsum failed for: " . implode(',', $keywords));
        }

        return null;
    }

    /**
     * Try Pexels (fallback).
     * Note: Requires API key for production, using placeholder fallback.
     */
    protected function tryPexels(array $keywords): ?string
    {
        // Pexels requires API key - use fallback service
        // For production, add API key in config
        $apiKey = config('services.pexels.key');

        if (empty($apiKey)) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $apiKey,
            ])->timeout(5)->get('https://api.pexels.com/v1/search', [
                'query' => implode(' ', $keywords),
                'per_page' => 1,
            ]);

            if ($response->successful() && !empty($response->json()['photos'])) {
                return $response->json()['photos'][0]['src']['large'];
            }
        } catch (\Exception $e) {
            Log::debug("Pexels failed: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Generate placeholder image URL (solid color with text).
     */
    protected function generatePlaceholderUrl(array $keywords): string
    {
        // Use placeholder.com or similar service
        $text = ucfirst(implode(' ', array_slice($keywords, 0, 2)));
        $colors = ['1a365d', '2d3748', '4a5568', '2c5282', '285e61'];

        return 'https://via.placeholder.com/800x600/' .
            $colors[array_rand($colors)] .
            '/ffffff?text=' . urlencode($text);
    }

    /**
     * Pre-generate multiple images for bulk content.
     */
    public function prewarmCache(array $titles, ?string $taxonomy = null): void
    {
        foreach ($titles as $title) {
            $cacheKey = 'auto_image:' . md5($title);
            if (!Cache::has($cacheKey)) {
                $image = $this->generateFromContext($title, $taxonomy);
                Cache::put($cacheKey, $image, self::CACHE_TTL);
            }
        }
    }
}