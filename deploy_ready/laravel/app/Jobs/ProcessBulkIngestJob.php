<?php

namespace App\Jobs;

use App\Models\Taxonomy;
use App\Models\Location;
use App\Models\ContentNode;
use App\Models\PostTemplate;
use App\Models\LiveDataVault;
use App\Helpers\SlugGenerator;
use App\Jobs\AnalyzeContentQualityJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProcessBulkIngestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying.
     */
    public int $backoff = 60;

    /**
     * The data payload from the API.
     */
    protected array $data;

    /**
     * Create a new job instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
        // Set queue connection based on data volume
        $this->onQueue('ingest');
    }

    /**
     * Execute the job - process in chunks to avoid database locks.
     */
    public function handle(): void
    {
        $startTime = microtime(true);
        $processed = ['taxonomies' => 0, 'locations' => 0, 'content_nodes' => 0, 'live_data' => 0, 'post_templates' => 0];

        // Process in chunks to prevent database locks
        if (!empty($this->data['taxonomies'])) {
            $chunks = array_chunk($this->data['taxonomies'], 100);
            foreach ($chunks as $chunk) {
                $this->processTaxonomies($chunk);
                $processed['taxonomies'] += count($chunk);
                // Release database connection between chunks
                usleep(10000);
            }
        }

        if (!empty($this->data['locations'])) {
            $chunks = array_chunk($this->data['locations'], 100);
            foreach ($chunks as $chunk) {
                $this->processLocations($chunk);
                $processed['locations'] += count($chunk);
                usleep(10000);
            }
        }

        if (!empty($this->data['post_templates'])) {
            $chunks = array_chunk($this->data['post_templates'], 50);
            foreach ($chunks as $chunk) {
                $this->processTemplates($chunk);
                $processed['post_templates'] += count($chunk);
                usleep(10000);
            }
        }

        if (!empty($this->data['live_data'])) {
            $chunks = array_chunk($this->data['live_data'], 100);
            foreach ($chunks as $chunk) {
                $this->processLiveData($chunk);
                $processed['live_data'] += count($chunk);
            }
        }

        // Content nodes processed last as they depend on other entities
        if (!empty($this->data['content_nodes'])) {
            $chunks = array_chunk($this->data['content_nodes'], 50);
            foreach ($chunks as $chunk) {
                $this->processContentNodes($chunk);
                $processed['content_nodes'] += count($chunk);
                usleep(10000);
            }
        }

        $duration = round(microtime(true) - $startTime, 2);
        Log::info('Bulk ingest job completed', [
            'processed' => $processed,
            'duration_seconds' => $duration,
            'queue' => $this->queue,
        ]);

        // Dispatch follow-up job for sitemap refresh
        SitemapRefreshJob::dispatch()->delay(now()->addMinutes(2));

        // Ping search engines about new content
        try {
            app(\App\Services\PingService::class)->pingSearchEngines();
        } catch (\Exception $e) {
            Log::warning('Bulk ingest ping failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Process taxonomy chunk with parent resolution.
     */
    protected function processTaxonomies(array $taxonomies): void
    {
        $slugMap = [];

        // First pass: create all and build slug map
        foreach ($taxonomies as $tax) {
            $parentId = null;
            if (!empty($tax['parent_slug'])) {
                $parentId = $slugMap[$tax['parent_slug']] ?? null;
            }
            $taxonomy = Taxonomy::updateOrCreate(
                ['slug' => $tax['slug']],
                ['name' => $tax['name'], 'parent_id' => $parentId]
            );
            $slugMap[$tax['slug']] = $taxonomy->id;
        }

        // Second pass: update parent IDs if they were pending
        foreach ($taxonomies as $tax) {
            if (!empty($tax['parent_slug']) && isset($slugMap[$tax['slug']]) && isset($slugMap[$tax['parent_slug']])) {
                Taxonomy::where('id', $slugMap[$tax['slug']])->update(['parent_id' => $slugMap[$tax['parent_slug']]]);
            }
        }
    }

    /**
     * Process location chunk with parent resolution.
     */
    protected function processLocations(array $locations): void
    {
        $slugMap = [];

        foreach ($locations as $loc) {
            $parentId = null;
            if (!empty($loc['parent_slug'])) {
                $parentId = $slugMap[$loc['parent_slug']] ?? null;
            }
            $location = Location::updateOrCreate(
                ['slug' => $loc['slug']],
                ['name' => $loc['name'], 'parent_id' => $parentId]
            );
            $slugMap[$loc['slug']] = $location->id;
        }

        foreach ($locations as $loc) {
            if (!empty($loc['parent_slug']) && isset($slugMap[$loc['slug']]) && isset($slugMap[$loc['parent_slug']])) {
                Location::where('id', $slugMap[$loc['slug']])->update(['parent_id' => $slugMap[$loc['parent_slug']]]);
            }
        }
    }

    /**
     * Process post templates.
     */
    protected function processTemplates(array $templates): void
    {
        $taxonomyMap = [];

        if (!empty($this->data['taxonomies'])) {
            foreach ($this->data['taxonomies'] as $t) {
                $taxonomyMap[$t['slug']] = $t['id'] ?? null;
            }
        }

        foreach ($templates as $tmpl) {
            $taxonomyId = null;
            if (!empty($tmpl['taxonomy_slug'])) {
                $tax = Taxonomy::where('slug', $tmpl['taxonomy_slug'])->first();
                $taxonomyId = $tax?->id;
            }
            PostTemplate::updateOrCreate(
                ['slug' => $tmpl['slug']],
                [
                    'name' => $tmpl['name'],
                    'template_body' => $tmpl['template_body'],
                    'taxonomy_id' => $taxonomyId
                ]
            );
        }
    }

    /**
     * Process live data vault entries.
     */
    protected function processLiveData(array $liveData): void
    {
        foreach ($liveData as $ld) {
            LiveDataVault::updateOrCreate(
                ['key' => $ld['key']],
                ['value' => $ld['value'], 'display_name' => $ld['display_name'] ?? null]
            );
        }
    }

    /**
     * Process content nodes with dependency resolution.
     */
    protected function processContentNodes(array $nodes): void
    {
        foreach ($nodes as $node) {
            try {
                $taxonomy = Taxonomy::firstOrCreate(
                    ['slug' => $node['taxonomy_slug']],
                    ['name' => Str::title(str_replace('-', ' ', $node['taxonomy_slug']))]
                );
                $location = Location::where('slug', $node['location_slug'])->firstOrFail();

                $templateId = null;
                if (!empty($node['template_slug'])) {
                    $template = PostTemplate::where('slug', $node['template_slug'])->first();
                    $templateId = $template?->id;
                }

                // Auto-generate slug if not provided
                $slug = $node['slug'] ?? null;
                if (empty($slug)) {
                    $slug = SlugGenerator::make($node['title'], $taxonomy->id, $location->id);
                } else {
                    $slug = SlugGenerator::generateUnique($slug, $taxonomy->id, $location->id);
                }

                // Generate UUID if not provided
                $uuid = $node['uuid'] ?? Str::uuid()->toString();

                $contentNode = ContentNode::updateOrCreate(
                    ['slug' => $slug, 'taxonomy_id' => $taxonomy->id, 'location_id' => $location->id],
                    [
                        'uuid' => $uuid,
                        'seo_title' => $node['title'],
                        'body_content' => $node['body_content'],
                        'meta_description' => $node['meta_description'] ?? null,
                        'featured_image' => $node['featured_image'] ?? null,
                        'post_template_id' => $templateId,
                        'is_restricted_content' => $node['is_restricted_content'] ?? false,
                        'publish_date' => $node['publish_date'] ?? now(),
                    ]
                );

                AnalyzeContentQualityJob::dispatch($contentNode);
            } catch (\Exception $e) {
                Log::warning('Content node ingestion failed', [
                    'slug' => $node['slug'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Bulk ingest job failed', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}