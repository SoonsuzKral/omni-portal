<?php

namespace App\Services;

use App\Models\ContentNode;
use App\Models\Keyword;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CrawlPriorityEngine
{
    const CACHE_TTL = 3600;
    const CACHE_KEY_PREFIX = 'crawl_priority:';
    const SCORE_MIN = 0.00;
    const SCORE_MAX = 99.99;

    /**
     * Calculate crawl priority score for a single content node.
     * Returns [score, breakdown].
     */
    public function calculate(ContentNode $content): array
    {
        $keyword = $this->resolveKeyword($content);

        $searchVolumeScore = $this->scoreSearchVolume($keyword);
        $trendMomentumScore = $this->scoreTrendMomentum($keyword);
        $monetizationScore = $this->scoreMonetization($content);
        $freshnessScore = $this->scoreFreshness($content);
        $linkAuthorityScore = $this->scoreLinkAuthority($content);
        $ctrPredictionScore = $this->scoreCtrPrediction($keyword);

        $rawScore = (
            ($searchVolumeScore * 0.20) +
            ($trendMomentumScore * 0.15) +
            ($monetizationScore * 0.20) +
            ($freshnessScore * 0.15) +
            ($linkAuthorityScore * 0.15) +
            ($ctrPredictionScore * 0.15)
        );

        $finalScore = round(min(self::SCORE_MAX, max(self::SCORE_MIN, $rawScore)), 2);

        $breakdown = [
            'search_volume' => round($searchVolumeScore, 2),
            'trend_momentum' => round($trendMomentumScore, 2),
            'monetization' => round($monetizationScore, 2),
            'freshness' => round($freshnessScore, 2),
            'link_authority' => round($linkAuthorityScore, 2),
            'ctr_prediction' => round($ctrPredictionScore, 2),
            'raw_score' => round($rawScore, 2),
            'calculated_at' => now()->toIso8601String(),
        ];

        return [$finalScore, $breakdown];
    }

    /**
     * Persist score + breakdown to the database.
     */
    public function persist(ContentNode $content): void
    {
        [$score, $breakdown] = $this->calculate($content);

        $content->crawl_priority_score = $score;
        $content->crawl_priority_breakdown = $breakdown;
        $content->saveQuietly();

        Cache::put(
            self::CACHE_KEY_PREFIX . $content->id,
            ['score' => $score, 'breakdown' => $breakdown],
            self::CACHE_TTL
        );
    }

    /**
     * Batch recalculate and persist scores.
     * Returns count of processed records.
     */
    public function batchRecalculate(int $chunkSize = 500, ?int $limit = null): int
    {
        $query = ContentNode::whereNotNull('publish_date');

        if ($limit) {
            $query->limit($limit);
        }

        $processed = 0;

        $query->chunk($chunkSize, function ($contents) use (&$processed) {
            foreach ($contents as $content) {
                $this->persist($content);
                $processed++;
            }
        });

        $this->clearScoreCache();

        return $processed;
    }

    /**
     * Get cached score for a content node.
     */
    public function getCachedScore(ContentNode $content): array
    {
        return Cache::remember(
            self::CACHE_KEY_PREFIX . $content->id,
            self::CACHE_TTL,
            fn () => [
                'score' => $content->crawl_priority_score ?? 0,
                'breakdown' => $content->crawl_priority_breakdown,
            ]
        );
    }

    /**
     * Score factor: Search Volume (0-100).
     * Uses Keyword.search_volume; falls back to page_views.
     */
    protected function scoreSearchVolume(?Keyword $keyword): float
    {
        if ($keyword && $keyword->search_volume > 0) {
            return min(100, ($keyword->search_volume / 1000) * 100);
        }
        return 10;
    }

    /**
     * Score factor: Trend Momentum (0-100).
     * Trending keywords + recent creation boost.
     */
    protected function scoreTrendMomentum(?Keyword $keyword): float
    {
        $score = 10;
        if ($keyword && $keyword->is_trending) {
            $score += 60;
        }
        if ($keyword && $keyword->created_at && $keyword->created_at->gt(now()->subDays(7))) {
            $score += 20;
        }
        return min(100, $score);
    }

    /**
     * Score factor: Monetization Potential (0-100).
     * ads_enabled, not restricted, high impression keyword.
     */
    protected function scoreMonetization(ContentNode $content): float
    {
        $score = 20;
        if ($content->ads_enabled) {
            $score += 30;
        }
        if (!$content->is_restricted_content) {
            $score += 25;
        }
        if ($content->locale === 'TR') {
            $score += 15;
        }
        return min(100, $score);
    }

    /**
     * Score factor: Freshness (0-100).
     * Newer = higher score. Decays over 90 days.
     */
    protected function scoreFreshness(ContentNode $content): float
    {
        if (!$content->publish_date) {
            return 10;
        }

        $daysSincePublish = $content->publish_date->diffInDays(now());
        if ($daysSincePublish <= 1) return 100;
        if ($daysSincePublish <= 7) return 90;
        if ($daysSincePublish <= 30) return 70;
        if ($daysSincePublish <= 60) return 50;
        if ($daysSincePublish <= 90) return 30;

        return max(5, 100 - ($daysSincePublish / 3));
    }

    /**
     * Score factor: Internal Link Authority (0-100).
     * Based on page_views, incoming link potential.
     */
    protected function scoreLinkAuthority(ContentNode $content): float
    {
        $views = $content->page_views ?? 0;
        $relatedCount = $content->relatedNodes()->count();

        $viewScore = min(60, log10($views + 1) * 15);
        $relatedScore = min(40, $relatedCount * 5);

        return min(100, $viewScore + $relatedScore);
    }

    /**
     * Score factor: CTR Prediction (0-100).
     * Based on Keyword clicks/impressions ratio.
     */
    protected function scoreCtrPrediction(?Keyword $keyword): float
    {
        if (!$keyword) {
            return 10;
        }

        $impressions = max(1, $keyword->impressions ?? 0);
        $clicks = $keyword->clicks ?? 0;
        $ctr = ($clicks / $impressions) * 100;

        if ($ctr >= 10) return 100;
        if ($ctr >= 5) return 80;
        if ($ctr >= 2) return 60;
        if ($ctr >= 1) return 40;

        $position = $keyword->position ?? 50;
        $positionScore = max(0, 50 - $position);

        return max(5, $positionScore);
    }

    /**
     * Resolve the associated Keyword for a content node.
     */
    protected function resolveKeyword(ContentNode $content): ?Keyword
    {
        return Keyword::where('slug', $content->slug)
            ->orWhere(function ($q) use ($content) {
                $q->where('category_id', $content->taxonomy_id)
                  ->where('location_id', $content->location_id);
            })
            ->first();
    }

    /**
     * Get aggregate priority stats for dashboard.
     */
    public function getAggregateStats(): array
    {
        $cacheKey = 'crawl_priority:aggregate';

        return Cache::remember($cacheKey, 600, function () {
            $avg = ContentNode::whereNotNull('publish_date')
                ->avg('crawl_priority_score');

            $high = ContentNode::whereNotNull('publish_date')
                ->where('crawl_priority_score', '>=', 70)
                ->count();

            $medium = ContentNode::whereNotNull('publish_date')
                ->whereBetween('crawl_priority_score', [40, 69.99])
                ->count();

            $low = ContentNode::whereNotNull('publish_date')
                ->where('crawl_priority_score', '<', 40)
                ->count();

            $uncounted = ContentNode::whereNotNull('publish_date')
                ->whereNull('crawl_priority_score')
                ->count();

            $topPages = ContentNode::whereNotNull('publish_date')
                ->orderBy('crawl_priority_score', 'desc')
                ->limit(5)
                ->with(['taxonomy:id,name', 'location:id,name'])
                ->get(['id', 'slug', 'seo_title', 'crawl_priority_score', 'taxonomy_id', 'location_id'])
                ->toArray();

            return [
                'average_score' => round($avg ?? 0, 2),
                'distribution' => [
                    'high_priority' => $high,
                    'medium_priority' => $medium,
                    'low_priority' => $low,
                    'unscored' => $uncounted,
                ],
                'top_pages' => $topPages,
                'total_scored' => ContentNode::whereNotNull('crawl_priority_score')->count(),
                'calculated_at' => now()->toIso8601String(),
            ];
        });
    }

    /**
     * Clear all cached priority scores.
     */
    public function clearScoreCache(): void
    {
        Cache::forget('crawl_priority:aggregate');
        ContentNode::whereNotNull('publish_date')
            ->select('id')
            ->chunk(500, function ($contents) {
                foreach ($contents as $content) {
                    Cache::forget(self::CACHE_KEY_PREFIX . $content->id);
                }
            });
    }

    /**
     * Count total published content.
     */
    public function getTotalPublishedCount(): int
    {
        return ContentNode::whereNotNull('publish_date')->count();
    }
}
