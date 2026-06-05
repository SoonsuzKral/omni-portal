<?php

namespace App\Jobs;

use App\Models\ContentNode;
use App\Services\AdaptivePriorityEngine;
use App\Services\SemanticLinkMatrix;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TelemetryFeedbackLoopJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 900;
    public int $tries = 2;

    public function __construct(
        public ?int $limit = null,
        public int $chunkSize = 200,
    ) {}

    public function handle(
        AdaptivePriorityEngine $adaptiveEngine,
        ?SemanticLinkMatrix $linkMatrix = null,
    ): void {
        $startTime = microtime(true);

        Log::info('TelemetryFeedbackLoopJob started', [
            'limit' => $this->limit,
            'chunk_size' => $this->chunkSize,
        ]);

        $query = ContentNode::whereNotNull('publish_date')
            ->whereNotNull('gsc_last_synced_at')
            ->orderBy('gsc_last_synced_at', 'desc');

        if ($this->limit) {
            $query->limit($this->limit);
        }

        $processed = 0;
        $query->chunkById($this->chunkSize, function ($contents) use ($adaptiveEngine, $linkMatrix, &$processed) {
            foreach ($contents as $content) {
                try {
                    $adaptiveEngine->persist($content);

                    if ($linkMatrix) {
                        Cache::forget("semantic_links:{$content->id}:20");
                    }

                    $processed++;
                } catch (\Exception $e) {
                    Log::warning('FeedbackLoop item failed', [
                        'content_id' => $content->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });

        $adaptiveEngine->clearCache();

        $elapsed = round(microtime(true) - $startTime, 2);

        Log::info('TelemetryFeedbackLoopJob completed', [
            'processed' => $processed,
            'elapsed_seconds' => $elapsed,
            'throughput' => $elapsed > 0 ? round($processed / $elapsed, 2) : 0,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('TelemetryFeedbackLoopJob failed', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
