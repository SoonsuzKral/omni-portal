<?php

namespace App\Console;

use App\Jobs\ContentWaveJob;
use App\Jobs\RecalculatePriorityJob;
use App\Jobs\SitemapRefreshJob;
use App\Jobs\SyncGscDataJob;
use App\Jobs\DetectAnomaliesJob;
use App\Jobs\TelemetryFeedbackLoopJob;
use App\Services\PingService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->job(new SitemapRefreshJob)->hourly();

        $schedule->job(new ContentWaveJob(500, 30))->everyThirtyMinutes();

        $schedule->job(new RecalculatePriorityJob())->dailyAt('03:00');

        $schedule->job(new SyncGscDataJob(
            startDate: now()->subDays(2)->toDateString(),
            endDate: now()->toDateString(),
            captureSnapshot: true,
        ))->dailyAt('02:00');

        $schedule->job(new SyncGscDataJob(
            startDate: now()->subDays(7)->toDateString(),
            endDate: now()->toDateString(),
            captureSnapshot: true,
        ))->weeklyOn(0, '05:00');

        $schedule->job(new DetectAnomaliesJob())->dailyAt('06:00');

        $schedule->job(new TelemetryFeedbackLoopJob(limit: 10000))->dailyAt('07:00');

        $schedule->call(function () {
            try {
                app(PingService::class)->pingSearchEngines();
            } catch (\Exception $e) {
                logger()->warning('Scheduled ping failed', ['error' => $e->getMessage()]);
            }
        })->dailyAt('04:00');
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
