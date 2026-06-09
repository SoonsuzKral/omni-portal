<?php

namespace App\Services;

use App\Models\ContentNode;
use App\Models\Taxonomy;
use App\Models\Location;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class SeoService
{
    const CACHE_TTL = 3600;

    public function generateTitle(ContentNode $content, ?Location $location = null, ?Taxonomy $taxonomy = null): string
    {
        $parts = [];

        if (!empty($content->seo_title)) {
            $parts[] = $content->seo_title;
        }

        if ($location && !Str::contains($content->seo_title ?? '', $location->name)) {
            $parts[] = $location->name;
        }

        if ($taxonomy && !Str::contains($content->seo_title ?? '', $taxonomy->name)) {
            $parts[] = $taxonomy->name;
        }

        return implode(' - ', $parts);
    }

    public function generateDescription(ContentNode $content, ?int $maxLength = 160): string
    {
        if (!empty($content->meta_description)) {
            return Str::limit($content->meta_description, $maxLength);
        }

        $cleanBody = strip_tags($content->body_content ?? '');
        $cleanBody = preg_replace('/\s+/', ' ', $cleanBody);
        $cleanBody = trim($cleanBody);

        $sentences = preg_split('/[.!?]+/', $cleanBody, -1, PREG_SPLIT_NO_EMPTY);
        if (!empty($sentences)) {
            $firstSentence = trim($sentences[0]);
            if (strlen($firstSentence) > 60) {
                return Str::limit($firstSentence, $maxLength);
            }
        }

        return Str::limit($cleanBody, $maxLength);
    }

    /**
     * Generate a unique meta description per content node using template variety.
     * Each page gets a deterministic but different template based on its slug hash.
     */
    public function generateUniqueMetaDescription(ContentNode $content, ?Location $location = null, ?Taxonomy $taxonomy = null): string
    {
        $city = $location?->name ?? 'Şehir';
        $district = $location?->parent?->name ?? $city;
        $category = $taxonomy?->name ?? 'Hizmet';

        if (!empty($content->meta_description)) {
            $resolved = str_replace(['{city}', '{district}', '{category}'], [$city, $district, $category], $content->meta_description);
            return Str::limit($resolved, 160);
        }

        $templates = [
            "{$city} ilinde {$category} hizmeti arıyorsanız doğru yerdesiniz. 7/24 profesyonel {$category} ekibimiz hizmetinizde.",
            "{$city} {$category} için en güvenilir adres. Hızlı, kaliteli ve uygun fiyatlı {$category} hizmeti.",
            "{$district} ve {$city} genelinde {$category} hizmetleri. Deneyimli ekip, garantili iş.",
            "{$city}'de {$category} sorununuz mu var? Uzman ekibimizle hızlı çözüm. Ücretsiz keşif.",
            "Kaliteli {$category} hizmeti için {$city}'nin tercih ettiği adres. Hemen ara, hemen gel.",
            "{$city} {$category} konusunda uzman kadromuzla hizmetinizdeyiz. Uygun fiyat, garantili hizmet.",
            "Profesyonel {$category} ekibimiz {$city} genelinde 7/24 hizmet vermektedir. Hemen teklif alın.",
            "{$city}'de güvenilir {$category} arayışınıza son nokta. Memnuniyet garantili profesyonel çözümler.",
        ];

        $index = abs(crc32($content->slug ?? $content->id)) % count($templates);
        return $templates[$index];
    }

    public function generateOgData(ContentNode $content, ?Location $location, ?Taxonomy $taxonomy): array
    {
        $description = $this->generateDescription($content, 200);

        return [
            'title' => $this->generateTitle($content, $location, $taxonomy),
            'description' => $description,
            'type' => 'article',
            'url' => $this->generateCanonicalUrl($content, $location, $taxonomy),
            'image' => $content->featured_image ?? $this->getDefaultOgImage($taxonomy),
            'locale' => 'tr_TR',
            'site_name' => config('app.name', 'Omni Portal'),
        ];
    }

    public function generateCanonicalUrl(ContentNode $content, ?Location $location, ?Taxonomy $taxonomy): string
    {
        $segments = [];

        if ($taxonomy) {
            $segments[] = $taxonomy->slug;
        }

        if ($location) {
            $segments[] = $location->slug;
        }

        $segments[] = $content->slug;

        return url('/' . implode('/', $segments));
    }

    public function generateJsonLd(ContentNode $content, ?Location $location, ?Taxonomy $taxonomy): array
    {
        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $content->seo_title ?? $content->title,
            'description' => $this->generateDescription($content),
            'datePublished' => $content->publish_date?->toIso8601String(),
            'dateModified' => $content->updated_at?->toIso8601String(),
            'author' => [
                '@type' => 'Organization',
                'name' => config('app.name'),
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => config('app.name'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset('logo.png'),
                ],
            ],
        ];

        if ($content->featured_image) {
            $jsonLd['image'] = $content->featured_image;
        }

        $jsonLd['breadcrumb'] = $this->generateBreadcrumb($content, $location, $taxonomy);

        if ($taxonomy) {
            $jsonLd['articleSection'] = $taxonomy->name;
        }

        if ($content->page_views > 0) {
            $jsonLd['interactionStatistic'] = [
                '@type' => 'InteractionCounter',
                'interactionType' => 'https://schema.org/ViewAction',
                'userInteractionCount' => $content->page_views,
            ];
        }

        return $jsonLd;
    }

    public function generateWebsiteSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => config('app.name'),
            'url' => url('/'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => url('/search?q={search_term_string}'),
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    public function generateFaqSchema(ContentNode $content): ?array
    {
        $faqData = $content->faq_data ?? null;

        if (!$faqData || !is_array($faqData)) {
            return null;
        }

        $faqItems = [];
        foreach ($faqData as $item) {
            if (isset($item['question']) && isset($item['answer'])) {
                $faqItems[] = [
                    '@type' => 'Question',
                    'name' => $item['question'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $item['answer'],
                    ],
                ];
            }
        }

        if (empty($faqItems)) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $faqItems,
        ];
    }

    public function generateLocalBusinessSchema(ContentNode $content, ?Location $location): ?array
    {
        if (!$location) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => $location->name . ' ' . ($content->taxonomy?->name ?? 'Services'),
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => $location->name,
                'addressRegion' => $location->parent?->name,
                'addressCountry' => 'TR',
            ],
            'areaServed' => [
                '@type' => 'City',
                'name' => $location->name,
            ],
        ];
    }

    public function generateBreadcrumb(ContentNode $content, ?Location $location, ?Taxonomy $taxonomy): array
    {
        $items = [];

        $items[] = [
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'Anasayfa',
            'item' => url('/'),
        ];

        if ($taxonomy) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => $taxonomy->name,
                'item' => url('/' . $taxonomy->slug),
            ];
        }

        if ($location) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => $location->name,
                'item' => url('/' . ($taxonomy?->slug ?? '') . '/' . $location->slug),
            ];
        }

        $items[] = [
            '@type' => 'ListItem',
            'position' => count($items) + 1,
            'name' => $content->title,
            'item' => $this->generateCanonicalUrl($content, $location, $taxonomy),
        ];

        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }

    public function generateTaxonomySeo(Taxonomy $taxonomy, ?Location $location = null, ?int $contentCount = null): array
    {
        $title = $taxonomy->name;
        if ($location) {
            $title = "{$location->name} {$taxonomy->name}";
        }

        $locationName = $location?->name ?? 'Türkiye';
        $description = "{$title} hizmetleri ve rehberi. {$locationName} genelinde en güncel içerikler.";
        if ($contentCount) {
            $description .= " {$contentCount} içerik.";
        }

        return [
            'title' => $title,
            'description' => Str::limit($description, 160),
            'og_title' => $title,
            'og_description' => Str::limit($description, 200),
        ];
    }

    protected function getDefaultOgImage(?Taxonomy $taxonomy): ?string
    {
        $path = public_path('og-default.png');
        return file_exists($path) ? asset('og-default.png') : asset('og-default.svg');
    }

    public function prewarmCache(): void
    {
        $popularContent = ContentNode::where('page_views', '>', 0)
            ->orderBy('page_views', 'desc')
            ->limit(100)
            ->get();

        foreach ($popularContent as $content) {
            $cacheKey = "seo:content:{$content->id}";
            Cache::put($cacheKey, [
                'title' => $this->generateTitle($content, $content->location, $content->taxonomy),
                'description' => $this->generateDescription($content),
            ], self::CACHE_TTL);
        }
    }
}