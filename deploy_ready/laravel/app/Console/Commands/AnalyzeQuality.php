<?php

namespace App\Console\Commands;

use App\Jobs\AnalyzeContentQualityJob;
use App\Jobs\ProcessQualityBatchJob;
use App\Models\ContentNode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

class AnalyzeQuality extends Command
{
    protected $signature = 'seo:analyze-quality
        {--chunk= : Content items per chunk}
        {--id= : Analyze specific content node by ID}
        {--batch : Dispatch as batched job}
        {--engines=all : Comma-separated engines: semantic,eeat,humanization,topic,satisfaction,depth,spam}
        {--unscored-only : Only analyze content missing quality scores}';

    protected $description = 'Analyze content quality across all quality engines';

    public function handle(): int
    {
        $chunkSize = (int) ($this->option('chunk') ?? config('quality-engine.chunk_size', 100));
        $contentId = $this->option('id');
        $engines = $this->option('engines') === 'all' ? ['all'] : explode(',', $this->option('engines'));
        $unscoredOnly = $this->option('unscored-only');

        if ($contentId) {
            $content = ContentNode::find($contentId);
            if (!$content) {
                $this->error("Content node #{$contentId} not found.");
                return Command::FAILURE;
            }
            AnalyzeContentQualityJob::dispatch($content, $engines);
            $this->info("Dispatched quality analysis for content #{$contentId}.");
            return Command::SUCCESS;
        }

        $query = ContentNode::whereNotNull('body_content');

        if ($unscoredOnly) {
            $query->whereNull('last_quality_analyzed_at');
        }

        $total = $query->count();
        $this->info("Dispatching quality analysis for {$total} content nodes...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $batchJobs = [];

        $query->chunk($chunkSize, function ($contents) use ($engines, $chunkSize, &$batchJobs, $bar) {
            $ids = $contents->pluck('id')->toArray();
            $batchJobs[] = new ProcessQualityBatchJob($ids, $engines);
            $bar->advance(count($contents));
        });

        $bar->finish();
        $this->newLine();

        if ($this->option('batch')) {
            Bus::batch($batchJobs)
                ->name('Quality Analysis Batch')
                ->allowFailures()
                ->dispatch();
            $this->info('Dispatched ' . count($batchJobs) . ' batch jobs as a single batch.');
        } else {
            foreach ($batchJobs as $job) {
                dispatch($job);
            }
            $this->info('Dispatched ' . count($batchJobs) . ' batch jobs to queue.');
        }

        return Command::SUCCESS;
    }
}
