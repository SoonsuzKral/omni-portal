<?php

namespace App\Jobs;

use App\Models\ContentNode;
use App\Services\IndexingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class IndexContentBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(
        public array $contentIds
    ) {
        $this->onQueue('indexing');
    }

    public function handle(IndexingService $indexingService): void
    {
        $startTime = microtime(true);

        $results = $indexingService->processBatchInstant($this->contentIds);

        Log::info("IndexContentBatch completed", [
            'total' => $results['total'],
            'successful' => $results['successful'],
            'failed' => $results['failed'],
            'elapsed' => $results['elapsed_seconds'],
            'throughput' => $results['urls_per_second'],
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("IndexContentBatch failed", [
            'content_ids' => $this->contentIds,
            'error' => $exception->getMessage(),
        ]);
    }
}