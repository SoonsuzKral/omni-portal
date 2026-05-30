<?php

namespace App\Jobs;

use App\Models\ContentNode;
use App\Services\QualityEngines\SemanticUniquenessEngine;
use App\Services\QualityEngines\EeatSignalEngine;
use App\Services\QualityEngines\HumanizationEngine;
use App\Services\QualityEngines\TopicAuthorityEngine;
use App\Services\QualityEngines\UserSatisfactionEngine;
use App\Services\QualityEngines\ContentDepthOrchestrator;
use App\Services\QualityEngines\AntiSpamRiskEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AnalyzeContentQualityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries = 2;

    public function __construct(
        public ContentNode $contentNode,
        public array $engines = ['all']
    ) {}

    public function handle(
        SemanticUniquenessEngine $semanticEngine,
        EeatSignalEngine $eeatEngine,
        HumanizationEngine $humanizationEngine,
        TopicAuthorityEngine $topicEngine,
        UserSatisfactionEngine $satisfactionEngine,
        ContentDepthOrchestrator $depthEngine,
        AntiSpamRiskEngine $spamEngine,
    ): void {
        try {
            $enginesToRun = $this->engines;

            if (in_array('all', $enginesToRun) || in_array('semantic', $enginesToRun)) {
                $semanticEngine->analyze($this->contentNode);
            }

            if (in_array('all', $enginesToRun) || in_array('eeat', $enginesToRun)) {
                $eeatEngine->analyze($this->contentNode);
            }

            if (in_array('all', $enginesToRun) || in_array('humanization', $enginesToRun)) {
                $humanizationEngine->analyze($this->contentNode);
            }

            if (in_array('all', $enginesToRun) || in_array('topic', $enginesToRun)) {
                $topicEngine->analyzeContentNode($this->contentNode);
            }

            if (in_array('all', $enginesToRun) || in_array('satisfaction', $enginesToRun)) {
                $satisfactionEngine->analyze($this->contentNode);
            }

            if (in_array('all', $enginesToRun) || in_array('depth', $enginesToRun)) {
                $depthEngine->analyze($this->contentNode);
            }

            if (in_array('all', $enginesToRun) || in_array('spam', $enginesToRun)) {
                $spamEngine->analyze($this->contentNode);
            }

            $this->contentNode->updateQuietly([
                'last_quality_analyzed_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Quality analysis failed', [
                'content_id' => $this->contentNode->id,
                'engines' => $this->engines,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->fail($e);
        }
    }

    public function tags(): array
    {
        return ['quality', 'content:'.$this->contentNode->id];
    }
}
