<?php

namespace App\Jobs;

use App\Services\AnomalyDetectionEngine;
use App\Services\IndexCoverageMonitor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DetectAnomaliesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;
    public int $tries = 2;

    public function __construct(
        public bool $includeSitemapCheck = true,
    ) {}

    public function handle(
        AnomalyDetectionEngine $anomalyEngine,
        ?IndexCoverageMonitor $coverageMonitor = null,
    ): void {
        $startTime = microtime(true);

        Log::info('DetectAnomaliesJob started');

        $results = $anomalyEngine->detectAll();

        if ($coverageMonitor && $this->includeSitemapCheck) {
            try {
                $coverageMonitor->captureSnapshot();
            } catch (\Exception $e) {
                Log::warning('Snapshot capture during anomaly detection failed', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $elapsed = round(microtime(true) - $startTime, 2);
        $total = collect($results)->flatten()->count();

        Log::info('DetectAnomaliesJob completed', [
            'total_anomalies' => $total,
            'elapsed_seconds' => $elapsed,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('DetectAnomaliesJob failed', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
