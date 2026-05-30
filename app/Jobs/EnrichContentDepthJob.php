<?php

namespace App\Jobs;

use App\Models\ContentNode;
use App\Services\QualityEngines\ContentDepthOrchestrator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EnrichContentDepthJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;
    public int $tries = 2;

    public function __construct(
        public ContentNode $contentNode,
        public bool $applyEnrichment = false
    ) {}

    public function handle(ContentDepthOrchestrator $depthOrchestrator): void
    {
        try {
            $result = $depthOrchestrator->analyze($this->contentNode);

            $shallowThreshold = config('quality-engine.depth.min_word_count', 800) / 2;

            if ($result->depth_score < 40 && $this->applyEnrichment) {
                Log::info('Enrichment needed for shallow content', [
                    'content_id' => $this->contentNode->id,
                    'depth_score' => $result->depth_score,
                    'suggestions' => count($result->enrichment_suggestions ?? []),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Content depth enrichment failed', [
                'content_id' => $this->contentNode->id,
                'error' => $e->getMessage(),
            ]);
            $this->fail($e);
        }
    }

    public function tags(): array
    {
        return ['quality', 'depth', 'content:'.$this->contentNode->id];
    }
}
