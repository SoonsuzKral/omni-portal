<?php

namespace App\Jobs;

use App\Models\ContentNode;
use App\Services\SearchTelemetryEngine;
use App\Services\AdaptivePriorityEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessTelemetryBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries = 2;

    public function __construct(
        public int $contentNodeId,
        public string $action = 'reprocess',
    ) {}

    public function handle(
        SearchTelemetryEngine $telemetryEngine,
        AdaptivePriorityEngine $adaptiveEngine,
    ): void {
        $content = ContentNode::find($this->contentNodeId);
        if (!$content) {
            Log::warning('ProcessTelemetryBatchJob: content not found', [
                'id' => $this->contentNodeId,
            ]);
            return;
        }

        try {
            match ($this->action) {
                'inspect' => $this->inspectUrl($content, $telemetryEngine),
                'reprocess' => $this->reprocessPriority($content, $adaptiveEngine),
                'feedback' => $this->feedbackLoop($content, $adaptiveEngine),
                default => $this->reprocessPriority($content, $adaptiveEngine),
            };
        } catch (\Exception $e) {
            Log::error('ProcessTelemetryBatchJob failed', [
                'content_id' => $this->contentNodeId,
                'action' => $this->action,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function inspectUrl(ContentNode $content, SearchTelemetryEngine $telemetryEngine): void
    {
        $result = $telemetryEngine->updateUrlInspection($content);
        if ($result) {
            Log::debug('URL inspected', [
                'content_id' => $content->id,
                'index_status' => $result['index_status'],
            ]);
        }
    }

    protected function reprocessPriority(ContentNode $content, AdaptivePriorityEngine $adaptiveEngine): void
    {
        $adaptiveEngine->persist($content);
    }

    protected function feedbackLoop(ContentNode $content, AdaptivePriorityEngine $adaptiveEngine): void
    {
        $adaptiveEngine->persist($content);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessTelemetryBatchJob failed fatally', [
            'content_id' => $this->contentNodeId,
            'action' => $this->action,
            'error' => $exception->getMessage(),
        ]);
    }
}
