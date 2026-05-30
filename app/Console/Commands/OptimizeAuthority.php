<?php

namespace App\Console\Commands;

use App\Jobs\OptimizeAuthorityJob;
use App\Models\Taxonomy;
use App\Models\ContentNode;
use App\Models\TopicAuthorityScore;
use Illuminate\Console\Command;

class OptimizeAuthority extends Command
{
    protected $signature = 'seo:optimize-authority
        {--taxonomy-id= : Optimize specific taxonomy}
        {--skip-propagation : Skip entity authority propagation}
        {--unscored-only : Only optimize topics without authority scores}
        {--chunk= : Taxonomy items per chunk}';

    protected $description = 'Optimize topical authority scores across the platform';

    public function handle(): int
    {
        $taxonomyId = $this->option('taxonomy-id');
        $skipPropagation = $this->option('skip-propagation');
        $unscoredOnly = $this->option('unscored-only');
        $chunkSize = (int) ($this->option('chunk') ?? 50);

        if ($taxonomyId) {
            OptimizeAuthorityJob::dispatch((int) $taxonomyId, !$skipPropagation);
            $this->info("Dispatched authority optimization for taxonomy #{$taxonomyId}.");
            return Command::SUCCESS;
        }

        $query = Taxonomy::query();

        if ($unscoredOnly) {
            $scoredIds = TopicAuthorityScore::where('topicable_type', Taxonomy::class)
                ->pluck('topicable_id')->toArray();
            $query->whereNotIn('id', $scoredIds);
        }

        $total = $query->count();
        $this->info("Optimizing authority for {$total} taxonomies...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $query->chunk($chunkSize, function ($taxonomies) use ($skipPropagation, $bar) {
            foreach ($taxonomies as $taxonomy) {
                OptimizeAuthorityJob::dispatch($taxonomy->id, !$skipPropagation);
            }
            $bar->advance(count($taxonomies));
        });

        $bar->finish();
        $this->newLine();

        $contentScored = ContentNode::whereNotNull('topic_coverage_score')->count();
        $taxonomyScored = TopicAuthorityScore::where('topicable_type', Taxonomy::class)->count();

        $this->table(['Metric', 'Count'], [
            ['Content nodes with authority scores', $contentScored],
            ['Taxonomies with authority scores', $taxonomyScored],
        ]);

        $this->info('Authority optimization dispatched to queue.');

        return Command::SUCCESS;
    }
}
