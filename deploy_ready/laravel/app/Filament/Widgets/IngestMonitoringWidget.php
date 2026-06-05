<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class IngestMonitoringWidget extends Widget
{
    protected static string $view = 'filament.widgets.ingest-monitoring-widget';

    protected int $columns = 2;

    public static function canView(): bool
    {
        return true;
    }

    public function getQueueStats(): array
    {
        try {
            $redis = Redis::connection();
            $ingestQueueSize = $redis->llen('queues:default:ingest') ?? 0;
            $processingCount = DB::table('jobs')
                ->where('queue', 'ingest')
                ->where('attempts', '>', 0)
                ->whereNull('reserved_at')
                ->count();

            return [
                'pending' => (int) $ingestQueueSize,
                'processing' => (int) $processingCount,
            ];
        } catch (\Exception $e) {
            return [
                'pending' => 0,
                'processing' => 0,
            ];
        }
    }

    public function getRecentIngestStats(): array
    {
        try {
            $last24h = DB::table('jobs')
                ->where('queue', 'ingest')
                ->where('created_at', '>=', now()->subHours(24))
                ->count();

            $lastHour = DB::table('jobs')
                ->where('queue', 'ingest')
                ->where('created_at', '>=', now()->subHour())
                ->count();

            return [
                'last_24h' => $last24h,
                'last_hour' => $lastHour,
            ];
        } catch (\Exception $e) {
            return [
                'last_24h' => 0,
                'last_hour' => 0,
            ];
        }
    }
}