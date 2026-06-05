<?php

namespace App\Services;

use App\Models\ContentNode;
use App\Models\Taxonomy;
use App\Models\Location;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SemanticLinkMatrix
{
    const CACHE_TTL = 1800; // 30 minutes

    protected array $externalPortals = [];

    public function __construct()
    {
        $this->externalPortals = config('seo.external_portals', [
            [
                'name' => 'Adult Portal',
                'base_url' => config('seo.adult_portal_url', 'https://adult.nexus'),
                'enabled' => config('seo.enable_adult_portal_link', false),
                'categories' => ['adult', '18+', 'mature'],
                'authority' => 'high',
            ],
            [
                'name' => 'Matrix Network',
                'base_url' => config('seo.matrix_portal_url', 'https://matrix.nexus'),
                'enabled' => config('seo.enable_matrix_link', false),
                'categories' => ['tech', 'digital', 'network'],
                'authority' => 'medium',
            ],
        ]);
    }

    /**
     * Generate semantic link matrix for a content node.
     * Returns organized internal links for SEO juice distribution.
     */
    public function generateLinks(ContentNode $content, ?int $limit = 20): array
    {
        $cacheKey = "semantic_links:{$content->id}:{$limit}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($content, $limit) {
            return [
                // Same taxonomy, different locations (horizontal links)
                'same_category_different_location' => $this->getSameTaxonomyDifferentLocations($content, $limit),

                // Same location, different taxonomy (contextual links)
                'same_location_different_category' => $this->getSameLocationDifferentTaxonomies($content, $limit),

                // Sibling locations (same parent city)
                'sibling_locations' => $this->getSiblingLocations($content, $limit),

                // Related taxonomies (parent/child)
                'related_categories' => $this->getRelatedTaxonomies($content, $limit),

                // High-performing content in same niche
                'popular_in_niche' => $this->getPopularInNiche($content, $limit),

                // Inter-portal synergy links
                'external_portals' => $this->getExternalPortalLinks($content),
            ];
        });
    }

    public function getExternalPortalLinks(ContentNode $content): array
    {
        $taxonomySlug = $content->taxonomy?->slug ?? '';
        $locationSlug = $content->location?->slug ?? '';

        $portalLinks = [];

        foreach ($this->externalPortals as $portal) {
            if (!$portal['enabled']) {
                continue;
            }

            $relevance = $this->calculatePortalRelevance($content, $portal);

            if ($relevance['score'] > 0.3) {
                $portalLinks[] = [
                    'name' => $portal['name'],
                    'url' => "{$portal['base_url']}/{$taxonomySlug}/{$locationSlug}",
                    'authority' => $portal['authority'],
                    'relevance_score' => $relevance['score'],
                    'reason' => $relevance['reason'],
                    'type' => 'external',
                ];
            }
        }

        return $portalLinks;
    }

    protected function calculatePortalRelevance(ContentNode $content, array $portal): array
    {
        $taxonomyName = Str::lower($content->taxonomy?->name ?? '');
        $score = 0;
        $reason = '';

        foreach ($portal['categories'] as $category) {
            if (Str::contains($taxonomyName, $category)) {
                $score = 1.0;
                $reason = "Matching category: {$category}";
                break;
            }
        }

        if ($score === 0 && $content->page_views > 1000) {
            $score = 0.5;
            $reason = 'High traffic page - cross-promotion opportunity';
        }

        return ['score' => $score, 'reason' => $reason];
    }

    public function renderCrossPortalSection(ContentNode $content): string
    {
        $links = $this->getExternalPortalLinks($content);

        if (empty($links)) {
            return '';
        }

        $html = '<div class="cross-portal-section" style="margin: 2rem 0; padding: 1rem; background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); border-radius: 12px;">';
        $html .= '<h3 style="color: #fff; margin-bottom: 1rem; font-size: 1.1rem;">🌐 Popular Local Directories</h3>';
        $html .= '<div style="display: flex; gap: 1rem; flex-wrap: wrap;">';

        foreach ($links as $link) {
            $authorityClass = $link['authority'] === 'high' ? 'badge-success' : 'badge-info';
            $html .= "<a href=\"{$link['url']}\" class=\"btn btn-outline-light btn-sm\" style=\"display: inline-flex; align-items: center; gap: 0.5rem;\">";
            $html .= "<span class=\"badge {$authorityClass}\">{$link['authority']}</span>";
            $html .= $link['name'];
            $html .= '</a>';
        }

        $html .= '</div></div>';

        return $html;
    }

    public function getSidebarCrossLinks(ContentNode $content, int $limit = 5): array
    {
        return Cache::remember("sidebar_cross_links:{$content->id}", 3600, function () use ($content, $limit) {
            $links = [];

            $trendingTaxonomies = Taxonomy::whereNotNull('parent_id')
                ->inRandomOrder()
                ->limit(3)
                ->get();

            foreach ($trendingTaxonomies as $taxonomy) {
                $location = $content->location ?? Location::inRandomOrder()->first();

                $portalUrl = config('seo.adult_portal_url', 'https://adult.nexus');
                $links[] = [
                    'title' => "Best in {$taxonomy->name}",
                    'url' => "{$portalUrl}/{$taxonomy->slug}/{$location?->slug}",
                    'icon' => 'arrow-trending-up',
                ];
            }

            return array_slice($links, 0, $limit);
        });
    }

    public function logInterPortalClick(ContentNode $content, string $portalName): void
    {
        DB::table('inter_portal_analytics')->updateOrInsert(
            [
                'content_id' => $content->id,
                'portal_name' => $portalName,
                'date' => now()->toDateString(),
            ],
            [
                'clicks' => DB::raw('clicks + 1'),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Same category, different location - great for cross-location SEO.
     */
    protected function getSameTaxonomyDifferentLocations(ContentNode $content, int $limit): array
    {
        $nodes = ContentNode::where('taxonomy_id', $content->taxonomy_id)
            ->where('id', '!=', $content->id)
            ->whereNotNull('publish_date')
            ->with(['location', 'taxonomy'])
            ->inRandomOrder()
            ->limit($limit)
            ->get();

        return $nodes->map(function ($node) {
            return [
                'title' => $node->title,
                'url' => $this->buildUrl($node),
                'location' => $node->location?->name,
                'views' => $node->page_views,
                'type' => 'horizontal',
            ];
        })->toArray();
    }

    /**
     * Same location, different category - contextual linking.
     */
    protected function getSameLocationDifferentTaxonomies(ContentNode $content, int $limit): array
    {
        if (!$content->location_id) {
            return [];
        }

        $nodes = ContentNode::where('location_id', $content->location_id)
            ->where('taxonomy_id', '!=', $content->taxonomy_id)
            ->where('id', '!=', $content->id)
            ->whereNotNull('publish_date')
            ->with(['location', 'taxonomy'])
            ->inRandomOrder()
            ->limit($limit)
            ->get();

        return $nodes->map(function ($node) {
            return [
                'title' => $node->title,
                'url' => $this->buildUrl($node),
                'category' => $node->taxonomy?->name,
                'views' => $node->page_views,
                'type' => 'contextual',
            ];
        })->toArray();
    }

    /**
     * Sibling locations - same parent city/district.
     */
    protected function getSiblingLocations(ContentNode $content, int $limit): array
    {
        if (!$content->location_id || !$content->location?->parent_id) {
            return [];
        }

        $siblings = Location::where('parent_id', $content->location->parent_id)
            ->where('id', '!=', $content->location_id)
            ->pluck('id');

        $nodes = ContentNode::whereIn('location_id', $siblings)
            ->where('taxonomy_id', $content->taxonomy_id)
            ->whereNotNull('publish_date')
            ->with(['location', 'taxonomy'])
            ->inRandomOrder()
            ->limit($limit)
            ->get();

        return $nodes->map(function ($node) {
            return [
                'title' => $node->title,
                'url' => $this->buildUrl($node),
                'location' => $node->location?->name,
                'type' => 'sibling',
            ];
        })->toArray();
    }

    /**
     * Related taxonomies - parent/child categories.
     */
    protected function getRelatedTaxonomies(ContentNode $content, int $limit): array
    {
        $taxonomyIds = [$content->taxonomy_id];

        // Add parent taxonomy
        if ($content->taxonomy?->parent_id) {
            $taxonomyIds[] = $content->taxonomy->parent_id;
        }

        // Add child taxonomies
        $children = Taxonomy::where('parent_id', $content->taxonomy_id)->pluck('id');
        $taxonomyIds = array_merge($taxonomyIds, $children->toArray());

        $nodes = ContentNode::whereIn('taxonomy_id', $taxonomyIds)
            ->where('id', '!=', $content->id)
            ->whereNotNull('publish_date')
            ->with(['location', 'taxonomy'])
            ->inRandomOrder()
            ->limit($limit)
            ->get();

        return $nodes->map(function ($node) {
            return [
                'title' => $node->title,
                'url' => $this->buildUrl($node),
                'category' => $node->taxonomy?->name,
                'relationship' => $node->taxonomy_id === $node->taxonomy?->parent_id ? 'parent' : 'child',
                'type' => 'hierarchical',
            ];
        })->toArray();
    }

    /**
     * Popular content in the same niche for "Popular Reads" section.
     */
    protected function getPopularInNiche(ContentNode $content, int $limit): array
    {
        $nodes = ContentNode::where('taxonomy_id', $content->taxonomy_id)
            ->where('id', '!=', $content->id)
            ->whereNotNull('publish_date')
            ->with(['location', 'taxonomy'])
            ->orderBy('page_views', 'desc')
            ->limit($limit)
            ->get();

        return $nodes->map(function ($node) {
            return [
                'title' => $node->title,
                'url' => $this->buildUrl($node),
                'location' => $node->location?->name,
                'views' => $node->page_views,
                'type' => 'popular',
            ];
        })->toArray();
    }

    /**
     * Build URL from content node.
     */
    protected function buildUrl(ContentNode $node): string
    {
        $taxonomySlug = $node->taxonomy?->slug ?? '';
        $locationSlug = $node->location?->slug ?? '';

        return url("/{$taxonomySlug}/{$locationSlug}/{$node->slug}");
    }

    /**
     * Get link juice score for internal linking decisions.
     */
    public function calculateLinkJuice(ContentNode $content): array
    {
        $baseScore = 100;

        // Age factor (newer = higher score)
        $daysSincePublish = $content->publish_date?->diffInDays(now()) ?? 0;
        $ageScore = max(0, 50 - ($daysSincePublish / 7)); // Decay over weeks

        // View factor (popular = higher score)
        $viewScore = min(30, log10($content->page_views + 1) * 10);

        // Location factor (unique location = higher score)
        $locationScore = $content->location_id ? 10 : 0;

        // Taxonomy factor
        $taxonomyScore = $content->taxonomy_id ? 10 : 0;

        return [
            'total_score' => $baseScore + $ageScore + $viewScore + $locationScore + $taxonomyScore,
            'breakdown' => [
                'base' => $baseScore,
                'age' => round($ageScore, 2),
                'views' => round($viewScore, 2),
                'location' => $locationScore,
                'taxonomy' => $taxonomyScore,
            ],
        ];
    }

    /**
     * Generate sitemap data for internal linking graph.
     */
    public function generateLinkGraph(int $limit = 1000): array
    {
        $nodes = ContentNode::whereNotNull('publish_date')
            ->with(['location', 'taxonomy'])
            ->orderBy('page_views', 'desc')
            ->limit($limit)
            ->get();

        $graph = [];

        foreach ($nodes as $node) {
            $graph[$node->id] = [
                'url' => $this->buildUrl($node),
                'taxonomy_id' => $node->taxonomy_id,
                'location_id' => $node->location_id,
                'link_juice' => $this->calculateLinkJuice($node)['total_score'],
            ];
        }

        return $graph;
    }
}