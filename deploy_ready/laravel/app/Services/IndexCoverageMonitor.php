<?php

namespace App\Services;

use App\Models\ContentNode;
use App\Models\IndexCoverage;
use App\Models\SearchConsoleTelemetry;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IndexCoverageMonitor
{
    const CACHE_PREFIX = 'index_coverage:';

    public function __construct(
        protected GoogleSearchConsoleService $gscService
    ) {}

    public function captureSnapshot(): array
    {
        $today = now()->toDateString();

        $submitted = ContentNode::whereNotNull('publish_date')->count();
        $indexed = ContentNode::where('gsc_index_status', 'INDEXED')->count();
        $coverageRatio = $submitted > 0 ? $indexed / $submitted : 0;

        $avgLatency = SearchConsoleTelemetry::whereNotNull('index_latency_minutes')
            ->avg('index_latency_minutes');
        $avgLatencySeconds = $avgLatency ? $avgLatency * 60 : 0;

        $yesterdayCoverage = IndexCoverage::where('snapshot_date', now()->subDay()->toDateString())->first();
        $indexingVelocity = 0;
        if ($yesterdayCoverage) {
            $deltaIndexed = $indexed - $yesterdayCoverage->indexed_urls;
            $indexingVelocity = $deltaIndexed;
        }

        $sitemapData = $this->gscService->isEnabled()
            ? $this->gscService->fetchSitemapList()
            : [];

        $sitemapCount = count($sitemapData);
        $sitemapIndexed = collect($sitemapData)->where('isPending', false)->count();
        $sitemapEfficiency = $sitemapCount > 0 ? $sitemapIndexed / $sitemapCount : 0;

        $crawlStats = $this->gscService->isEnabled()
            ? $this->gscService->fetchCrawlStats()
            : null;

        $telemetrySummary = SearchConsoleTelemetry::where('date', $today)->first();

        $orphanPages = $this->countOrphanPages();

        $breakdown = [
            'orphan_pages' => $orphanPages,
            'never_indexed' => ContentNode::whereNotNull('publish_date')
                ->whereNull('gsc_first_indexed_at')->count(),
            'synced_today' => ContentNode::whereDate('gsc_last_synced_at', $today)->count(),
        ];

        $snapshot = IndexCoverage::updateOrCreate(
            ['snapshot_date' => $today],
            [
                'submitted_urls' => $submitted,
                'indexed_urls' => $indexed,
                'coverage_ratio' => round($coverageRatio, 4),
                'avg_crawl_latency_seconds' => round($avgLatencySeconds, 2),
                'indexing_velocity' => round($indexingVelocity, 2),
                'sitemap_count' => $sitemapCount,
                'sitemap_indexed' => $sitemapIndexed,
                'sitemap_efficiency' => round($sitemapEfficiency, 4),
                'crawl_requests' => $crawlStats['crawl_requests'] ?? 0,
                'crawl_errors' => $crawlStats['crawl_errors'] ?? 0,
                'breakdown' => $breakdown,
            ]
        );

        Cache::forget(self::CACHE_PREFIX . 'latest');
        Cache::forget(self::CACHE_PREFIX . 'trend');

        Log::info('IndexCoverage snapshot captured', [
            'date' => $today,
            'submitted' => $submitted,
            'indexed' => $indexed,
            'coverage_ratio' => $coverageRatio,
        ]);

        return $snapshot->toArray();
    }

    public function countOrphanPages(): int
    {
        $indexedIds = SearchConsoleTelemetry::where('index_status', 'INDEXED')
            ->whereNotNull('content_node_id')
            ->distinct('content_node_id')
            ->pluck('content_node_id');

        $indexedFromGsc = ContentNode::whereIn('id', $indexedIds)->count();

        $submitted = ContentNode::whereNotNull('publish_date')->count();

        return max(0, $submitted - $indexedFromGsc);
    }

    public function getSubmittedVsIndexedRatio(): float
    {
        $latest = IndexCoverage::latest()->first();
        if (!$latest || $latest->submitted_urls === 0) {
            return 0;
        }
        return round(($latest->indexed_urls / $latest->submitted_urls) * 100, 2);
    }

    public function getAverageIndexingLatency(): float
    {
        $avg = SearchConsoleTelemetry::whereNotNull('index_latency_minutes')
            ->avg('index_latency_minutes');
        return round($avg ?? 0, 2);
    }

    public function getCrawlEfficiency(): float
    {
        $latest = IndexCoverage::latest()->first();
        if (!$latest || $latest->crawl_requests === 0) {
            return 0;
        }
        return round(
            ($latest->crawl_requests - $latest->crawl_errors) / $latest->crawl_requests * 100,
            2
        );
    }

    public function getTopPerformingClusters(int $limit = 10): array
    {
        return ContentNode::whereNotNull('publish_date')
            ->where('gsc_total_impressions', '>', 0)
            ->select(
                'taxonomy_id',
                DB::raw('COUNT(*) as page_count'),
                DB::raw('SUM(gsc_total_impressions) as total_impressions'),
                DB::raw('SUM(gsc_total_clicks) as total_clicks'),
                DB::raw('AVG(gsc_avg_position) as avg_position')
            )
            ->groupBy('taxonomy_id')
            ->orderByDesc(DB::raw('SUM(gsc_total_clicks)'))
            ->limit($limit)
            ->with('taxonomy:id,name')
            ->get()
            ->toArray();
    }

    public function getPagesNeverIndexed(int $limit = 100): array
    {
        return ContentNode::whereNotNull('publish_date')
            ->whereNull('gsc_first_indexed_at')
            ->with(['taxonomy:id,name', 'location:id,name'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get(['id', 'slug', 'seo_title', 'created_at', 'taxonomy_id', 'location_id'])
            ->toArray();
    }

    public function getPagesLosingImpressions(int $days = 30): array
    {
        return ContentNode::whereNotNull('publish_date')
            ->whereNotNull('gsc_last_impression_at')
            ->where('gsc_last_impression_at', '<', now()->subDays($days))
            ->where('gsc_total_impressions', '>', 0)
            ->orderBy('gsc_total_impressions', 'desc')
            ->limit(50)
            ->get(['id', 'slug', 'seo_title', 'gsc_total_impressions', 'gsc_total_clicks', 'gsc_last_impression_at', 'gsc_avg_position'])
            ->toArray();
    }

    public function getCoverageHistory(int $days = 30): array
    {
        return IndexCoverage::where('snapshot_date', '>=', now()->subDays($days))
            ->orderBy('snapshot_date')
            ->get(['snapshot_date', 'coverage_ratio', 'indexed_urls', 'submitted_urls', 'indexing_velocity'])
            ->toArray();
    }

    public function getSitemapEfficiency(): float
    {
        $latest = IndexCoverage::latest()->first();
        if (!$latest || $latest->sitemap_count === 0) {
            return 0;
        }
        return round($latest->sitemap_efficiency * 100, 2);
    }
}
