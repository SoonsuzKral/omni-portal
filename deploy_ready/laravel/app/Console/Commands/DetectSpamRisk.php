<?php

namespace App\Console\Commands;

use App\Jobs\DetectSpamRiskJob;
use App\Models\AntiSpamRiskScore;
use App\Models\ContentNode;
use Illuminate\Console\Command;

class DetectSpamRisk extends Command
{
    protected $signature = 'seo:detect-spam-risk
        {--chunk= : Content items per chunk}
        {--id= : Analyze specific content node by ID}
        {--threshold=60 : Only report content with spam risk above threshold}
        {--high-risk-only : Only analyze content not yet scored}';

    protected $description = 'Detect spam risk across all content nodes';

    public function handle(): int
    {
        $chunkSize = (int) ($this->option('chunk') ?? config('quality-engine.chunk_size', 100));
        $contentId = $this->option('id');
        $threshold = (float) $this->option('threshold');
        $highRiskOnly = $this->option('high-risk-only');

        if ($contentId) {
            $content = ContentNode::find($contentId);
            if (!$content) {
                $this->error("Content node #{$contentId} not found.");
                return Command::FAILURE;
            }
            DetectSpamRiskJob::dispatch($content);
            $this->info("Dispatched spam risk detection for content #{$contentId}.");
            return Command::SUCCESS;
        }

        $query = ContentNode::whereNotNull('body_content');

        if ($highRiskOnly) {
            $scoredIds = AntiSpamRiskScore::pluck('content_node_id')->toArray();
            $query->whereNotIn('id', $scoredIds);
        }

        $total = $query->count();
        $this->info("Dispatching spam risk detection for {$total} content nodes...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $query->chunk($chunkSize, function ($contents) use ($bar) {
            foreach ($contents as $content) {
                DetectSpamRiskJob::dispatch($content);
            }
            $bar->advance(count($contents));
        });

        $bar->finish();
        $this->newLine();

        $highRiskCount = AntiSpamRiskScore::where('overall_spam_risk_score', '>=', $threshold)->count();
        $this->warn("Content with spam risk >= {$threshold}: {$highRiskCount}");

        return Command::SUCCESS;
    }
}
