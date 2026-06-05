<?php

namespace App\Services;

use App\Models\AnomalyDetection;
use App\Models\ContentNode;
use App\Models\IndexCoverage;
use App\Models\SearchConsoleTelemetry;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnomalyDetectionEngine
{
    const CACHE_PREFIX = 'anomaly:';

    protected array $thresholds;
    protected int $cooldownHours;

    public function __construct()
    {
        $this->thresholds = config('search-telemetry.anomaly', []);
        $this->cooldownHours = (int) ($this->thresholds['cooldown_hours'] ?? 24);
    }

    public function detectAll(): array
    {
        $results = [];

        $results['deindexing'] = $this->detectSuddenDeindexing();
        $results['ctr_collapse'] = $this->detectCtrCollapse();
        $results['ranking_volatility'] = $this->detectRankingVolatility();
        $results['crawl_drops'] = $this->detectCrawlDrops();
        $results['sitemap_failures'] = $this->detectSitemapFetchFailures();

        Cache::put(self::CACHE_PREFIX . 'last_run', now(), 86400);

        $totalAnomalies = collect($results)->flatten()->count();
        Log::info('Anomaly detection completed', [
            'total_anomalies' => $totalAnomalies,
            'breakdown' => array_map('count', $results),
        ]);

        return $results;
    }

    protected function detectSuddenDeindexing(): array
    {
        $anomalies = [];
        $threshold = (float) ($this->thresholds['deindexing_drop_threshold'] ?? 50);

        $yesterdayCoverage = IndexCoverage::where('snapshot_date', now()->subDay()->toDateString())->first();
        $todayCoverage = IndexCoverage::where('snapshot_date', now()->toDateString())->first();

        if ($yesterdayCoverage && $todayCoverage) {
            $drop = $yesterdayCoverage->indexed_urls - $todayCoverage->indexed_urls;
            $dropPercent = $yesterdayCoverage->indexed_urls > 0
                ? ($drop / $yesterdayCoverage->indexed_urls) * 100
                : 0;

            if ($dropPercent > $threshold) {
                $anomaly = $this->recordAnomaly([
                    'content_node_id' => null,
                    'url' => null,
                    'anomaly_type' => 'sudden_deindexing',
                    'severity' => 'critical',
                    'current_value' => $todayCoverage->indexed_urls,
                    'previous_value' => $yesterdayCoverage->indexed_urls,
                    'threshold' => $threshold,
                    'deviation' => round($dropPercent, 2),
                    'description' => "Massive indexed URL drop: {$todayCoverage->indexed_urls} from {$yesterdayCoverage->indexed_urls} ({$dropPercent}%)",
                    'context' => [
                        'yesterday_indexed' => $yesterdayCoverage->indexed_urls,
                        'today_indexed' => $todayCoverage->indexed_urls,
                        'drop_count' => $drop,
                        'drop_percent' => round($dropPercent, 2),
                    ],
                ]);
                if ($anomaly) {
                    $anomalies[] = $anomaly;
                }
            }
        }

        ContentNode::where('gsc_index_status', 'INDEXED')
            ->whereNotNull('gsc_last_synced_at')
            ->chunk(500, function ($contents) use ($threshold, &$anomalies) {
                foreach ($contents as $content) {
                    $prevTelemetry = SearchConsoleTelemetry::where('content_node_id', $content->id)
                        ->where('index_status', 'INDEXED')
                        ->whereDate('date', '<', now()->subDays(7))
                        ->latest('date')
                        ->first();

                    if ($prevTelemetry) {
                        $currentStatus = $content->gsc_index_status;
                        if ($currentStatus !== 'INDEXED') {
                            $anomaly = $this->recordAnomaly([
                                'content_node_id' => $content->id,
                                'url' => $prevTelemetry->url,
                                'anomaly_type' => 'sudden_deindexing',
                                'severity' => 'critical',
                                'current_value' => 0,
                                'previous_value' => 1,
                                'threshold' => $threshold,
                                'deviation' => 100,
                                'description' => "Page lost indexed status: {$content->slug}",
                                'context' => [
                                    'slug' => $content->slug,
                                    'previous_status' => 'INDEXED',
                                    'current_status' => $currentStatus,
                                ],
                            ]);
                            if ($anomaly) {
                                $anomalies[] = $anomaly;
                            }
                        }
                    }
                }
            });

        return $anomalies;
    }

    protected function detectCtrCollapse(): array
    {
        $anomalies = [];
        $threshold = (float) ($this->thresholds['ctr_collapse_threshold'] ?? 0.5);

        ContentNode::where('gsc_total_impressions', '>', 100)
            ->chunk(500, function ($contents) use ($threshold, &$anomalies) {
                foreach ($contents as $content) {
                    $recentTelemetry = SearchConsoleTelemetry::where('content_node_id', $content->id)
                        ->whereDate('date', '>=', now()->subDays(7))
                        ->select(DB::raw('SUM(clicks) / SUM(impressions) as recent_ctr'))
                        ->first();

                    $previousTelemetry = SearchConsoleTelemetry::where('content_node_id', $content->id)
                        ->whereDate('date', '>=', now()->subDays(30))
                        ->whereDate('date', '<', now()->subDays(7))
                        ->select(DB::raw('SUM(clicks) / SUM(impressions) as previous_ctr'))
                        ->first();

                    if ($recentTelemetry && $previousTelemetry && $previousTelemetry->recent_ctr > 0) {
                        $recentCtr = (float) ($recentTelemetry->recent_ctr ?? 0);
                        $previousCtr = (float) ($previousTelemetry->recent_ctr ?? 0);
                        $ratio = $previousCtr > 0 ? $recentCtr / $previousCtr : 0;

                        if ($ratio < $threshold) {
                            $anomaly = $this->recordAnomaly([
                                'content_node_id' => $content->id,
                                'url' => $content->slug,
                                'anomaly_type' => 'ctr_collapse',
                                'severity' => 'warning',
                                'current_value' => round($recentCtr * 100, 4),
                                'previous_value' => round($previousCtr * 100, 4),
                                'threshold' => $threshold,
                                'deviation' => round((1 - $ratio) * 100, 2),
                                'description' => "CTR collapsed for {$content->slug}: {$recentCtr}% from {$previousCtr}%",
                                'context' => [
                                    'slug' => $content->slug,
                                    'recent_ctr' => round($recentCtr, 6),
                                    'previous_ctr' => round($previousCtr, 6),
                                    'ratio' => round($ratio, 4),
                                ],
                            ]);
                            if ($anomaly) {
                                $anomalies[] = $anomaly;
                            }
                        }
                    }
                }
            });

        return $anomalies;
    }

    protected function detectRankingVolatility(): array
    {
        $anomalies = [];
        $threshold = (float) ($this->thresholds['ranking_volatility_threshold'] ?? 10);

        ContentNode::where('gsc_total_impressions', '>', 50)
            ->whereNotNull('gsc_avg_position')
            ->chunk(500, function ($contents) use ($threshold, &$anomalies) {
                foreach ($contents as $content) {
                    $positions = SearchConsoleTelemetry::where('content_node_id', $content->id)
                        ->whereDate('date', '>=', now()->subDays(14))
                        ->where('avg_position', '>', 0)
                        ->orderBy('date')
                        ->pluck('avg_position');

                    if ($positions->count() >= 3) {
                        $avg = $positions->avg();
                        $variance = $positions->map(function ($p) use ($avg) {
                            return abs($p - $avg);
                        })->avg();

                        if ($variance > $threshold) {
                            $anomaly = $this->recordAnomaly([
                                'content_node_id' => $content->id,
                                'url' => $content->slug,
                                'anomaly_type' => 'ranking_volatility',
                                'severity' => 'warning',
                                'current_value' => $positions->last(),
                                'previous_value' => $positions->first(),
                                'threshold' => $threshold,
                                'deviation' => round($variance, 2),
                                'description' => "Ranking volatility detected for {$content->slug}: variance {$variance}",
                                'context' => [
                                    'slug' => $content->slug,
                                    'avg_position' => round($avg, 2),
                                    'variance' => round($variance, 2),
                                    'position_history' => $positions->toArray(),
                                ],
                            ]);
                            if ($anomaly) {
                                $anomalies[] = $anomaly;
                            }
                        }
                    }
                }
            });

        return $anomalies;
    }

    protected function detectCrawlDrops(): array
    {
        $anomalies = [];
        $threshold = (float) ($this->thresholds['crawl_drop_threshold'] ?? 30);

        $today = IndexCoverage::where('snapshot_date', now()->toDateString())->first();
        $yesterday = IndexCoverage::where('snapshot_date', now()->subDay()->toDateString())->first();

        if ($today && $yesterday && $yesterday->crawl_requests > 0) {
            $drop = $yesterday->crawl_requests - $today->crawl_requests;
            $dropPercent = ($drop / $yesterday->crawl_requests) * 100;

            if ($dropPercent > $threshold) {
                $anomaly = $this->recordAnomaly([
                    'content_node_id' => null,
                    'url' => null,
                    'anomaly_type' => 'crawl_drop',
                    'severity' => 'critical',
                    'current_value' => $today->crawl_requests,
                    'previous_value' => $yesterday->crawl_requests,
                    'threshold' => $threshold,
                    'deviation' => round($dropPercent, 2),
                    'description' => "Crawl requests dropped {$dropPercent}%: {$today->crawl_requests} from {$yesterday->crawl_requests}",
                    'context' => [
                        'yesterday_requests' => $yesterday->crawl_requests,
                        'today_requests' => $today->crawl_requests,
                        'drop_percent' => round($dropPercent, 2),
                    ],
                ]);
                if ($anomaly) {
                    $anomalies[] = $anomaly;
                }
            }
        }

        return $anomalies;
    }

    protected function detectSitemapFetchFailures(): array
    {
        $anomalies = [];
        $threshold = (int) ($this->thresholds['sitemap_fetch_fail_threshold'] ?? 3);

        $sitemaps = [];
        try {
            $gscService = app(GoogleSearchConsoleService::class);
            if ($gscService->isEnabled()) {
                $sitemaps = $gscService->fetchSitemapList();
            }
        } catch (\Exception $e) {
            $sitemaps = [];
        }

        foreach ($sitemaps as $sitemap) {
            $path = $sitemap['path'] ?? '';
            $lastDownloaded = $sitemap['lastDownloaded'] ?? null;
            $isPending = $sitemap['isPending'] ?? true;
            $errors = $sitemap['errors'] ?? 0;

            if ($errors > $threshold) {
                $anomaly = $this->recordAnomaly([
                    'content_node_id' => null,
                    'url' => $path,
                    'anomaly_type' => 'sitemap_fetch_failure',
                    'severity' => $errors > $threshold * 2 ? 'critical' : 'warning',
                    'current_value' => $errors,
                    'previous_value' => $threshold,
                    'threshold' => $threshold,
                    'deviation' => $errors - $threshold,
                    'description' => "Sitemap {$path} has {$errors} fetch errors",
                    'context' => [
                        'path' => $path,
                        'errors' => $errors,
                        'is_pending' => $isPending,
                        'last_downloaded' => $lastDownloaded,
                    ],
                ]);
                if ($anomaly) {
                    $anomalies[] = $anomaly;
                }
            }
        }

        return $anomalies;
    }

    protected function recordAnomaly(array $data): ?array
    {
        $cooldownUntil = now()->subHours($this->cooldownHours);

        $recent = AnomalyDetection::where('anomaly_type', $data['anomaly_type'])
            ->where('is_active', true)
            ->when($data['content_node_id'], fn ($q) => $q->where('content_node_id', $data['content_node_id']))
            ->when($data['url'], fn ($q) => $q->where('url', $data['url']))
            ->where('detected_at', '>=', $cooldownUntil)
            ->exists();

        if ($recent) {
            return null;
        }

        $anomaly = AnomalyDetection::create(array_merge($data, [
            'detected_at' => now(),
            'is_active' => true,
        ]));

        return $anomaly->toArray();
    }

    public function getActiveAnomalies(int $limit = 50): array
    {
        return AnomalyDetection::active()
            ->with('contentNode:id,slug,seo_title')
            ->orderBy('detected_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getAnomalyStats(): array
    {
        $cacheKey = self::CACHE_PREFIX . 'stats';

        return Cache::remember($cacheKey, 300, function () {
            $active = AnomalyDetection::active()->count();
            $critical = AnomalyDetection::active()->critical()->count();
            $warning = AnomalyDetection::active()->warning()->count();
            $info = AnomalyDetection::active()->info()->count();

            $byType = AnomalyDetection::active()
                ->select('anomaly_type', DB::raw('COUNT(*) as count'))
                ->groupBy('anomaly_type')
                ->pluck('count', 'anomaly_type')
                ->toArray();

            $recent = AnomalyDetection::active()
                ->where('detected_at', '>=', now()->subDay())
                ->count();

            return [
                'active_total' => $active,
                'critical' => $critical,
                'warning' => $warning,
                'info' => $info,
                'by_type' => $byType,
                'last_24h' => $recent,
                'last_run_at' => Cache::get(self::CACHE_PREFIX . 'last_run'),
            ];
        });
    }

    public function resolveAnomaly(int $id): bool
    {
        return AnomalyDetection::where('id', $id)->update([
            'is_active' => false,
            'resolved_at' => now(),
        ]);
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_PREFIX . 'stats');
    }
}
