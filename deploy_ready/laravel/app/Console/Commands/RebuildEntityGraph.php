<?php

namespace App\Console\Commands;

use App\Jobs\RebuildEntityGraphJob;
use App\Models\EntityAuthorityGraph;
use App\Models\ContentNode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RebuildEntityGraph extends Command
{
    protected $signature = 'seo:rebuild-entity-graph
        {--chunk= : Content items per chunk}
        {--content-id= : Analyze entities for specific content node}
        {--propagate : Run authority propagation after extraction}
        {--reset : Clear existing entity graph before rebuild}';

    protected $description = 'Rebuild the entity authority graph from all content nodes';

    public function handle(): int
    {
        $contentId = $this->option('content-id');
        $chunkSize = (int) ($this->option('chunk') ?? config('quality-engine.chunk_size', 100));

        if ($this->option('reset')) {
            if ($this->confirm('This will delete all entities and relationships. Continue?')) {
                DB::statement('DELETE FROM entity_relationships');
                DB::statement('DELETE FROM entity_authority_graph');
                $this->info('Entity graph cleared.');
            } else {
                $this->info('Cancelled.');
                return Command::SUCCESS;
            }
        }

        if ($contentId) {
            RebuildEntityGraphJob::dispatch($chunkSize, (int) $contentId);
            $this->info("Dispatched entity graph rebuild for content #{$contentId}.");
            return Command::SUCCESS;
        }

        RebuildEntityGraphJob::dispatch($chunkSize);
        $this->info('Entity graph rebuild dispatched to queue.');

        if ($this->option('propagate')) {
            $this->info('Authority propagation will run after extraction.');
        }

        $stats = [
            'entities' => EntityAuthorityGraph::count(),
            'relationships' => DB::table('entity_relationships')->count(),
        ];

        $this->table(['Metric', 'Count'], [
            ['Existing entities', $stats['entities']],
            ['Existing relationships', $stats['relationships']],
        ]);

        return Command::SUCCESS;
    }
}
