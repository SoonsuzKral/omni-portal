<?php

namespace App\Filament\Widgets;

use App\Services\IndexingService;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class QueueSpeedWidget extends BaseWidget
{
    protected int $columns = 4;

    protected function getStats(): array
    {
        $indexingService = app(IndexingService::class);
        $baseStats = $indexingService->getQueueStats();

        $contentLastHour = DB::table('content_nodes')
            ->whereNotNull('publish_date')
            ->where('created_at', '>=', now()->subHour())
            ->count();

        $contentLast24h = DB::table('content_nodes')
            ->whereNotNull('publish_date')
            ->where('created_at', '>=', now()->subHours(24))
            ->count();

        $failedJobs = 0;
        try {
            $failedJobs = DB::table('failed_jobs')
                ->where('failed_at', '>=', now()->subDay())
                ->count();
        } catch (\Exception $e) {
            // failed_jobs table may not exist or have different schema
        }

        $processingRatePerSec = $contentLastHour > 0 ? round($contentLastHour / 3600, 4) : 0;

        return [
            Stat::make('⚡ Per Second', $processingRatePerSec)
                ->description('Pages processed')
                ->color('success'),

            Stat::make('⏱️ Per Minute', round($contentLastHour / 60, 1))
                ->description('Processing rate')
                ->color('info'),

            Stat::make('📄 Last 24h', number_format($contentLast24h))
                ->description('Total content created')
                ->color('warning'),

            Stat::make('💥 Failed (24h)', $failedJobs)
                ->description($failedJobs > 0 ? 'Check queue:clear' : 'All clear')
                ->color($failedJobs > 0 ? 'danger' : 'success'),
        ];
    }
}