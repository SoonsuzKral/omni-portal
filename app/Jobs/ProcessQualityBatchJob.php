<?php

namespace App\Jobs;

use App\Models\ContentNode;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessQualityBatchJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;
    public int $tries = 1;

    public function __construct(
        public array $contentIds,
        public array $engines = ['all']
    ) {}

    public function handle(): void
    {
        if ($this->batch() && $this->batch()->cancelled()) {
            return;
        }

        $contents = ContentNode::whereIn('id', $this->contentIds)->get();

        foreach ($contents as $content) {
            AnalyzeContentQualityJob::dispatch($content, $this->engines)
                ->onConnection(config('quality-engine.queue.connection'))
                ->onQueue(config('quality-engine.queue.analyze_queue'));
        }

        Log::info('Quality batch dispatched', [
            'count' => count($this->contentIds),
            'engines' => $this->engines,
        ]);
    }

    public function tags(): array
    {
        return ['quality-batch'];
    }
}
