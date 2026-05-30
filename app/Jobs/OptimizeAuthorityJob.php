<?php

namespace App\Jobs;

use App\Models\ContentNode;
use App\Models\Taxonomy;
use App\Services\QualityEngines\TopicAuthorityEngine;
use App\Services\QualityEngines\EntityAuthorityGraphEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class OptimizeAuthorityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;
    public int $tries = 1;

    public function __construct(
        public ?int $taxonomyId = null,
        public bool $propagateAuthority = true
    ) {}

    public function handle(
        TopicAuthorityEngine $topicEngine,
        EntityAuthorityGraphEngine $graphEngine
    ): void {
        try {
            if ($this->taxonomyId) {
                $taxonomy = Taxonomy::find($this->taxonomyId);
                if ($taxonomy) {
                    $topicEngine->analyzeTaxonomy($taxonomy);
                    Log::info('Authority optimized for taxonomy', [
                        'taxonomy_id' => $taxonomy->id,
                        'name' => $taxonomy->name,
                    ]);
                }
            } else {
                Taxonomy::chunk(100, function ($taxonomies) use ($topicEngine) {
                    foreach ($taxonomies as $taxonomy) {
                        $topicEngine->analyzeTaxonomy($taxonomy);
                    }
                });

                if ($this->propagateAuthority) {
                    $updated = $graphEngine->propagateAuthority();
                    $topicalUpdated = $graphEngine->computeTopicalRelevance();
                    Log::info('Authority propagation complete', [
                        'entities_updated' => $updated,
                        'topical_relevance_updated' => $topicalUpdated,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Authority optimization failed', [
                'taxonomy_id' => $this->taxonomyId,
                'error' => $e->getMessage(),
            ]);
            $this->fail($e);
        }
    }

    public function tags(): array
    {
        return ['quality', 'authority'];
    }
}
