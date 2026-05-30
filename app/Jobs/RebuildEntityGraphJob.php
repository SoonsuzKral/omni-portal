<?php

namespace App\Jobs;

use App\Models\ContentNode;
use App\Services\QualityEngines\EntityAuthorityGraphEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RebuildEntityGraphJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;
    public int $tries = 1;

    public function __construct(
        public int $chunkSize = 100,
        public ?int $contentId = null
    ) {}

    public function handle(EntityAuthorityGraphEngine $graphEngine): void
    {
        try {
            if ($this->contentId) {
                $content = ContentNode::find($this->contentId);
                if ($content) {
                    $entities = $graphEngine->analyzeContentEntities($content);
                    Log::info('Entity graph updated for content', [
                        'content_id' => $content->id,
                        'entities_found' => count($entities),
                    ]);
                }
            } else {
                $stats = $graphEngine->rebuildGraph($this->chunkSize);
                Log::info('Entity graph rebuilt', $stats);
            }
        } catch (\Throwable $e) {
            Log::error('Entity graph rebuild failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->fail($e);
        }
    }

    public function tags(): array
    {
        return ['quality', 'entity-graph'];
    }
}
