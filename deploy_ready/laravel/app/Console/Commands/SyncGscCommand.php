<?php

namespace App\Console\Commands;

use App\Jobs\SyncGscDataJob;
use App\Jobs\TelemetryFeedbackLoopJob;
use App\Services\SearchTelemetryEngine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncGscCommand extends Command
{
    protected $signature = 'seo:sync-gsc
        {--days-back=30 : Number of days to sync}
        {--start-date= : Start date (Y-m-d)}
        {--end-date= : End date (Y-m-d)}
        {--inspect : Also run URL inspection for non-indexed pages}
        {--feedback : Run feedback loop after sync}
        {--no-snapshot : Skip index coverage snapshot}
        {--sync-only : Only sync analytics, skip inspection and feedback}';

    protected $description = 'Sync Google Search Console telemetry data';

    public function handle(SearchTelemetryEngine $telemetryEngine): int
    {
        $syncOnly = $this->option('sync-only');
        $inspect = $this->option('inspect') && !$syncOnly;
        $feedback = $this->option('feedback') && !$syncOnly;

        $startDate = $this->option('start-date');
        $endDate = $this->option('end-date');
        $daysBack = (int) $this->option('days-back');

        if (!$startDate) {
            $startDate = now()->subDays($daysBack)->toDateString();
        }
        if (!$endDate) {
            $endDate = now()->toDateString();
        }

        $this->info("Starting GSC sync from {$startDate} to {$endDate}...");

        SyncGscDataJob::dispatch(
            startDate: $startDate,
            endDate: $endDate,
            captureSnapshot: !$this->option('no-snapshot'),
            inspectUrls: $inspect,
        );

        $this->info('✓ SyncGscDataJob dispatched to queue');
        $this->line("  Start date: {$startDate}");
        $this->line("  End date:   {$endDate}");
        $this->line("  Inspect:    " . ($inspect ? 'yes' : 'no'));

        if ($feedback) {
            TelemetryFeedbackLoopJob::dispatch();
            $this->info('✓ TelemetryFeedbackLoopJob dispatched');
        }

        return self::SUCCESS;
    }
}
