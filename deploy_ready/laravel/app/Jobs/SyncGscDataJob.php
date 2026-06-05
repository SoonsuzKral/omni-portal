<?php

namespace App\Jobs;

use App\Services\GoogleSearchConsoleService;
use App\Services\SearchTelemetryEngine;
use App\Services\IndexCoverageMonitor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncGscDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800;
    public int $tries = 2;
    public int $backoff = 300;

    public function __construct(
        public ?string $startDate = null,
        public ?string $endDate = null,
        public bool $captureSnapshot = true,
        public bool $inspectUrls = false,
        public ?int $inspectLimit = null,
    ) {}

    public function handle(
        SearchTelemetryEngine $telemetryEngine,
        IndexCoverageMonitor $coverageMonitor,
    ): void {
        $startTime = microtime(true);
        $startDate = $this->startDate ?? now()->subDays(config('search-telemetry.sync.days_back', 30))->toDateString();
        $endDate = $this->endDate ?? now()->toDateString();

        Log::info('SyncGscDataJob started', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'inspect_urls' => $this->inspectUrls,
        ]);

        $syncStats = $telemetryEngine->syncTelemetry($startDate, $endDate);

        if ($this->captureSnapshot) {
            $snapshot = $coverageMonitor->captureSnapshot();
            Log::info('IndexCoverage snapshot captured', [
                'coverage_ratio' => $snapshot['coverage_ratio'] ?? 0,
            ]);
        }

        if ($this->inspectUrls) {
            $this->dispatchUrlInspections($telemetryEngine);
        }

        $elapsed = round(microtime(true) - $startTime, 2);

        Log::info('SyncGscDataJob completed', [
            'sync_stats' => $syncStats,
            'elapsed_seconds' => $elapsed,
        ]);
    }

    protected function dispatchUrlInspections(SearchTelemetryEngine $telemetryEngine): void
    {
        $query = \App\Models\ContentNode::whereNotNull('publish_date')
            ->whereNull('gsc_first_indexed_at')
            ->orWhere(function ($q) {
                $q->whereNull('gsc_index_status')
                  ->orWhere('gsc_index_status', '!=', 'INDEXED');
            });

        if ($this->inspectLimit) {
            $query->limit($this->inspectLimit);
        }

        $query->chunk(200, function ($contents) {
            foreach ($contents as $content) {
                ProcessTelemetryBatchJob::dispatch($content->id, 'inspect');
            }
        });
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SyncGscDataJob failed', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
