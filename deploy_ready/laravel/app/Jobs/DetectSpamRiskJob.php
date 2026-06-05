<?php

namespace App\Jobs;

use App\Models\ContentNode;
use App\Services\QualityEngines\AntiSpamRiskEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DetectSpamRiskJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;
    public int $tries = 2;

    public function __construct(
        public ContentNode $contentNode
    ) {}

    public function handle(AntiSpamRiskEngine $spamEngine): void
    {
        try {
            $result = $spamEngine->analyze($this->contentNode);

            if ($result->overall_spam_risk_score >= 70) {
                Log::warning('High spam risk content detected', [
                    'content_id' => $this->contentNode->id,
                    'title' => $this->contentNode->seo_title,
                    'spam_score' => $result->overall_spam_risk_score,
                    'risk_factors' => $result->risk_factors,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Spam risk detection failed', [
                'content_id' => $this->contentNode->id,
                'error' => $e->getMessage(),
            ]);
            $this->fail($e);
        }
    }

    public function tags(): array
    {
        return ['quality', 'spam', 'content:'.$this->contentNode->id];
    }
}
