<?php

namespace App\Services;

use App\Models\ContentNode;
use App\Models\SearchConsoleTelemetry;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class SearchTelemetryEngine
{
    const CACHE_PREFIX = 'telemetry:';

    public function __construct(
        protected GoogleSearchConsoleService $gscService
    ) {}

    public function syncTelemetry(string $startDate, string $endDate, int $chunkSize = 200): array
    {
        if (!$this->gscService->isEnabled()) {
            return ['status' => 'disabled', 'message' => 'GSC not enabled'];
        }

        $this->gscService->authenticate();

        $rows = $this->gscService->fetchQueryAnalytics($startDate, $endDate);
        $stats = ['total' => count($rows), 'upserted' => 0, 'failed' => 0];

        foreach (array_chunk($rows, $chunkSize) as $chunk) {
            try {
                $upserted = $this->upsertTelemetryChunk($chunk, $endDate);
                $stats['upserted'] += $upserted;
            } catch (\Exception $e) {
                $stats['failed'] += count($chunk);
                Log::error('Telemetry chunk failed', ['error' => $e->getMessage()]);
            }
        }

        $this->updateContentNodesFromTelemetry($startDate, $endDate);

        Cache::forget(self::CACHE_PREFIX . 'aggregate');

        return $stats;
    }

    protected function upsertTelemetryChunk(array $rows, string $date): int
    {
        $upserted = 0;
        $now = now();

        foreach ($rows as $row) {
            $url = $row['keys'][1] ?? null;
            if (!$url) {
                continue;
            }

            $path = parse_url($url, PHP_URL_PATH);
            $slug = trim($path ?? '', '/');
            $slugParts = explode('/', $slug);
            $slug = end($slugParts);

            $contentNode = ContentNode::where('slug', $slug)->first();

            $impressions = (int) ($row['impressions'] ?? 0);
            $clicks = (int) ($row['clicks'] ?? 0);
            $ctr = $impressions > 0 ? $clicks / $impressions : 0;
            $avgPosition = (float) ($row['averagePosition'] ?? 0);

            SearchConsoleTelemetry::updateOrCreate(
                [
                    'url' => $url,
                    'date' => $date,
                ],
                [
                    'content_node_id' => $contentNode?->id,
                    'impressions' => DB::raw('impressions + ' . $impressions),
                    'clicks' => DB::raw('clicks + ' . $clicks),
                    'ctr' => $ctr,
                    'avg_position' => $avgPosition,
                    'last_impression_at' => $impressions > 0 ? $now : null,
                    'last_click_at' => $clicks > 0 ? $now : null,
                    'source' => 'gsc',
                ]
            );

            $upserted++;
        }

        return $upserted;
    }

    public function updateContentNodesFromTelemetry(string $startDate, string $endDate): int
    {
        $updated = 0;
        $cacheKey = self::CACHE_PREFIX . 'last_telemetry_sync';

        $aggregates = SearchConsoleTelemetry::where('content_node_id', '!=', null)
            ->select(
                'content_node_id',
                DB::raw('SUM(impressions) as total_impressions'),
                DB::raw('SUM(clicks) as total_clicks'),
                DB::raw('AVG(avg_position) as avg_position'),
                DB::raw('MAX(last_impression_at) as last_impression_at'),
                DB::raw('MAX(last_click_at) as last_click_at'),
                DB::raw('MAX(CASE WHEN index_status = "INDEXED" THEN date END) as last_indexed_date')
            )
            ->groupBy('content_node_id')
            ->cursor();

        foreach ($aggregates as $agg) {
            ContentNode::where('id', $agg->content_node_id)->update([
                'gsc_total_impressions' => $agg->total_impressions,
                'gsc_total_clicks' => $agg->total_clicks,
                'gsc_avg_position' => round($agg->avg_position ?? 0, 2),
                'gsc_last_impression_at' => $agg->last_impression_at,
                'gsc_last_click_at' => $agg->last_click_at,
                'gsc_last_synced_at' => now(),
            ]);
            $updated++;
        }

        Cache::put($cacheKey, now(), 86400);

        return $updated;
    }

    public function updateUrlInspection(ContentNode $content): ?array
    {
        if (!$this->gscService->isEnabled()) {
            return null;
        }

        $url = url("/{$content->taxonomy?->slug}/{$content->location?->slug}/{$content->slug}");
        $result = $this->gscService->inspectUrl($url);

        if (!$result) {
            return null;
        }

        $inspection = $result['inspectionResult'] ?? [];
        $indexStatus = $inspection['indexStatusResult'] ?? [];

        $verdict = $indexStatus['verdict'] ?? 'UNSPECIFIED';
        $coverageState = $indexStatus['coverageState'] ?? null;
        $firstIndexed = isset($indexStatus['firstIndexed'])
            ? Carbon::parse($indexStatus['firstIndexed'])
            : null;
        $lastCrawled = isset($inspection['crawlResult']['lastCrawled'])
            ? Carbon::parse($inspection['crawlResult']['lastCrawled'])
            : null;

        $firstDiscovered = isset($inspection['crawlResult']['firstDiscovered'])
            ? Carbon::parse($inspection['crawlResult']['firstDiscovered'])
            : null;

        $indexLatency = null;
        if ($firstIndexed && $firstDiscovered) {
            $indexLatency = $firstIndexed->diffInMinutes($firstDiscovered);
        }

        $telemetryData = [
            'content_node_id' => $content->id,
            'url' => $url,
            'date' => now()->toDateString(),
            'first_discovered_at' => $firstDiscovered,
            'first_crawled_at' => $lastCrawled,
            'first_indexed_at' => $firstIndexed,
            'index_status' => $verdict,
            'index_latency_minutes' => $indexLatency,
            'source' => 'gsc_inspection',
            'raw_payload' => $result,
        ];

        SearchConsoleTelemetry::updateOrCreate(
            ['url' => $url, 'date' => now()->toDateString()],
            $telemetryData
        );

        $content->update([
            'gsc_first_discovered_at' => $firstDiscovered ?? $content->gsc_first_discovered_at,
            'gsc_first_crawled_at' => $lastCrawled ?? $content->gsc_first_crawled_at,
            'gsc_first_indexed_at' => $firstIndexed ?? $content->gsc_first_indexed_at,
            'gsc_index_status' => $verdict,
            'gsc_index_latency_minutes' => $indexLatency ?? $content->gsc_index_latency_minutes,
            'gsc_last_synced_at' => now(),
        ]);

        return [
            'url' => $url,
            'index_status' => $verdict,
            'coverage_state' => $coverageState,
            'first_indexed_at' => $firstIndexed,
            'index_latency_minutes' => $indexLatency,
        ];
    }

    public function getAggregateStats(): array
    {
        $cacheKey = self::CACHE_PREFIX . 'aggregate';

        return Cache::remember($cacheKey, config('search-telemetry.cache.ttl_seconds', 900), function () {
            $total = ContentNode::whereNotNull('publish_date')->count();
            $indexed = ContentNode::where('gsc_index_status', 'INDEXED')->count();
            $neverIndexed = ContentNode::whereNotNull('publish_date')
                ->whereNull('gsc_first_indexed_at')->count();
            $synced = ContentNode::whereNotNull('gsc_last_synced_at')->count();

            $avgPosition = ContentNode::where('gsc_total_impressions', '>', 0)
                ->avg('gsc_avg_position');

            $avgLatency = SearchConsoleTelemetry::whereNotNull('index_latency_minutes')
                ->avg('index_latency_minutes');

            $totalImpressions = SearchConsoleTelemetry::sum('impressions');
            $totalClicks = SearchConsoleTelemetry::sum('clicks');

            $topByCtr = ContentNode::where('gsc_total_impressions', '>', 100)
                ->select('id', 'slug', 'seo_title', 'gsc_total_impressions', 'gsc_total_clicks', 'gsc_avg_position')
                ->orderByRaw('(gsc_total_clicks / gsc_total_impressions) DESC')
                ->limit(10)
                ->get()
                ->toArray();

            $losingPages = ContentNode::whereNotNull('gsc_last_impression_at')
                ->where('gsc_last_impression_at', '<', now()->subDays(30))
                ->where('gsc_total_impressions', '>', 0)
                ->count();

            return [
                'total_urls' => $total,
                'indexed_urls' => $indexed,
                'never_indexed' => $neverIndexed,
                'synced_urls' => $synced,
                'coverage_percentage' => $total > 0 ? round(($indexed / $total) * 100, 2) : 0,
                'avg_position' => round($avgPosition ?? 0, 2),
                'avg_index_latency_minutes' => round($avgLatency ?? 0, 2),
                'total_impressions' => $totalImpressions,
                'total_clicks' => $totalClicks,
                'overall_ctr' => $totalImpressions > 0
                    ? round(($totalClicks / $totalImpressions) * 100, 4)
                    : 0,
                'top_performing_urls' => $topByCtr,
                'pages_losing_impressions' => $losingPages,
                'calculated_at' => now()->toIso8601String(),
            ];
        });
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_PREFIX . 'aggregate');
    }
}
