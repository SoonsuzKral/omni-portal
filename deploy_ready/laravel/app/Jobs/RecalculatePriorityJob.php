<?php

namespace App\Jobs;

use App\Services\CrawlPriorityEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RecalculatePriorityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 900;
    public int $tries = 2;

    public function __construct(
        public ?int $limit = null,
        public int $chunkSize = 500
    ) {}

    public function handle(CrawlPriorityEngine $engine): void
    {
        $startTime = microtime(true);

        $processed = $engine->batchRecalculate($this->chunkSize, $this->limit);

        $elapsed = round(microtime(true) - $startTime, 2);

        Log::info('RecalculatePriorityJob completed', [
            'processed' => $processed,
            'elapsed_seconds' => $elapsed,
            'throughput' => $elapsed > 0 ? round($processed / $elapsed, 2) : 0,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('RecalculatePriorityJob failed', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
