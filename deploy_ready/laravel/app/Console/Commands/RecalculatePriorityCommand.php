<?php

namespace App\Console\Commands;

use App\Jobs\RecalculatePriorityJob;
use App\Services\CrawlPriorityEngine;
use Illuminate\Console\Command;

class RecalculatePriorityCommand extends Command
{
    protected $signature = 'seo:recalculate-priority
        {--sync : Run synchronously instead of dispatching a job}
        {--limit= : Maximum number of content nodes to process}
        {--chunk=500 : Records per chunk}';

    protected $description = 'Recalculate crawl priority scores for all published content nodes';

    public function handle(CrawlPriorityEngine $engine): int
    {
        $sync = $this->option('sync');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;
        $chunk = (int) $this->option('chunk');

        $start = microtime(true);

        if ($sync) {
            $this->info('Running synchronously...');
            $bar = $this->output->createProgressBar(
                $limit ?? $engine->getTotalPublishedCount()
            );
            $bar->start();

            $processed = $engine->batchRecalculate($chunk, $limit);

            $bar->finish();
            $this->newLine();

            $elapsed = round(microtime(true) - $start, 2);
            $this->info("Processed {$processed} content nodes in {$elapsed}s");
        } else {
            RecalculatePriorityJob::dispatch($limit, $chunk);
            $this->info('RecalculatePriorityJob dispatched to queue.');
        }

        $this->call('cache:clear', ['--tags' => 'crawl_priority']);

        return Command::SUCCESS;
    }
}
