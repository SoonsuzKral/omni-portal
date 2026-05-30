<?php

namespace App\Services;

use App\Models\ContentNode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdaptivePriorityEngine
{
    const CACHE_PREFIX = 'adaptive_priority:';

    public function __construct(
        protected CrawlPriorityEngine $baseEngine,
        protected SearchTelemetryEngine $telemetryEngine
    ) {}

    public function calculate(ContentNode $content): array
    {
        [$baseScore, $breakdown] = $this->baseEngine->calculate($content);

        $telemetryAdjustments = $this->calculateTelemetryAdjustments($content);
        $breakdown = array_merge($breakdown, $telemetryAdjustments['breakdown']);

        $adjustedScore = $baseScore + $telemetryAdjustments['delta'];
        $finalScore = round(min(99.99, max(0.00, $adjustedScore)), 2);

        $breakdown['gsc_adjusted_score'] = $finalScore;
        $breakdown['gsc_delta'] = round($telemetryAdjustments['delta'], 2);
        $breakdown['gsc_calculated_at'] = now()->toIso8601String();

        return [$finalScore, $breakdown];
    }

    public function persist(ContentNode $content): void
    {
        [$score, $breakdown] = $this->calculate($content);

        $content->crawl_priority_score = $score;
        $content->crawl_priority_breakdown = $breakdown;
        $content->saveQuietly();

        Cache::put(
            self::CACHE_PREFIX . $content->id,
            ['score' => $score, 'breakdown' => $breakdown],
            3600
        );
    }

    public function batchRecalculate(int $chunkSize = 500, ?int $limit = null): int
    {
        $query = ContentNode::whereNotNull('publish_date');

        if ($limit) {
            $query->limit($limit);
        }

        $processed = 0;

        $query->chunkById($chunkSize, function ($contents) use (&$processed) {
            foreach ($contents as $content) {
                $this->persist($content);
                $processed++;
            }
        });

        $this->clearCache();

        return $processed;
    }

    protected function calculateTelemetryAdjustments(ContentNode $content): array
    {
        $delta = 0;
        $breakdown = [];
        $weights = config('search-telemetry.weights');

        if ($content->gsc_index_status === 'INDEXED') {
            $bonus = (float) ($weights['gsc_indexed_bonus'] ?? 15.0);
            $delta += $bonus;
            $breakdown['gsc_indexed_bonus'] = round($bonus, 2);
        } else {
            $penalty = (float) ($weights['gsc_non_indexed_penalty'] ?? -20.0);
            $delta += $penalty;
            $breakdown['gsc_non_indexed_penalty'] = round($penalty, 2);
        }

        if ($content->gsc_total_impressions > 0) {
            $ctr = ($content->gsc_total_clicks / $content->gsc_total_impressions) * 100;

            if ($ctr >= 10) {
                $boost = (float) ($weights['gsc_high_ctr_boost'] ?? 10.0);
                $delta += $boost;
                $breakdown['gsc_ctr_boost'] = round($boost, 2);
                $breakdown['gsc_ctr_pct'] = round($ctr, 2);
            }

            $staleDays = 90;
            if ($content->gsc_last_impression_at) {
                $daysSinceImpression = $content->gsc_last_impression_at->diffInDays(now());
                if ($daysSinceImpression > (int) ($weights['gsc_decay_days'] ?? 90)) {
                    $penalty = (float) ($weights['gsc_stale_penalty'] ?? -10.0);
                    $delta += $penalty;
                    $breakdown['gsc_stale_penalty'] = round($penalty, 2);
                    $breakdown['gsc_days_since_impression'] = $daysSinceImpression;
                }
            }

            if ($content->gsc_avg_position > 0 && $content->gsc_avg_position < 3) {
                $delta += 5;
                $breakdown['gsc_top_position_bonus'] = 5;
            }

            if ($content->gsc_avg_position > 20) {
                $delta -= 5;
                $breakdown['gsc_low_position_penalty'] = -5;
            }
        }

        $breakdown['gsc_delta'] = round($delta, 2);

        return ['delta' => $delta, 'breakdown' => $breakdown];
    }

    public function detectCrawlWaste(ContentNode $content): bool
    {
        if (!$content->crawl_priority_score || $content->gsc_index_status !== 'INDEXED') {
            return false;
        }

        $highPriorityButLowTraffic = $content->crawl_priority_score > 70
            && ($content->gsc_total_impressions ?? 0) < 10
            && $content->gsc_first_indexed_at
            && $content->gsc_first_indexed_at->diffInDays(now()) > 30;

        if ($highPriorityButLowTraffic) {
            return true;
        }

        return false;
    }

    public function autoAdjustSitemapPriority(ContentNode $content): float
    {
        $score = $content->crawl_priority_score ?? 0;

        if ($content->gsc_index_status === 'INDEXED' && $content->gsc_total_clicks > 0) {
            $score = min(99.99, $score + 5);
        }

        if ($this->detectCrawlWaste($content)) {
            $score = max(0, $score - 20);
        }

        if ($content->gsc_index_status !== 'INDEXED' && $content->created_at->diffInDays(now()) > 60) {
            $score = max(0, $score - 15);
        }

        return round($score, 2);
    }

    public function getAdaptiveStats(): array
    {
        $cacheKey = self::CACHE_PREFIX . 'aggregate';

        return Cache::remember($cacheKey, 600, function () {
            $avgGscDelta = ContentNode::whereNotNull('publish_date')
                ->whereNotNull('gsc_last_synced_at')
                ->select(DB::raw('AVG(JSON_EXTRACT(crawl_priority_breakdown, "$.gsc_delta")) as avg_delta'))
                ->value('avg_delta');

            $wasteCount = 0;
            ContentNode::whereNotNull('publish_date')
                ->whereNotNull('gsc_index_status')
                ->where('crawl_priority_score', '>', 70)
                ->where('gsc_total_impressions', '<', 10)
                ->chunk(500, function ($chunk) use (&$wasteCount) {
                    foreach ($chunk as $content) {
                        if ($this->detectCrawlWaste($content)) {
                            $wasteCount++;
                        }
                    }
                });

            $adjustedCount = ContentNode::whereNotNull('publish_date')
                ->whereNotNull('gsc_last_synced_at')
                ->count();

            return [
                'avg_gsc_delta' => round($avgGscDelta ?? 0, 2),
                'crawl_waste_pages' => $wasteCount,
                'telemetry_adjusted_count' => $adjustedCount,
                'total_scored' => ContentNode::whereNotNull('crawl_priority_score')->count(),
            ];
        });
    }

    public function clearCache(): void
    {
        Cache::forget('crawl_priority:aggregate');
        Cache::forget(self::CACHE_PREFIX . 'aggregate');
    }
}
