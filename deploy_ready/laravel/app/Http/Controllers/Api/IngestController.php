<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessBulkIngestJob;
use App\Jobs\AnalyzeContentQualityJob;
use App\Helpers\SlugGenerator;
use App\Models\Taxonomy;
use App\Models\Location;
use App\Models\ContentNode;
use App\Models\PostTemplate;
use App\Models\LiveDataVault;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class IngestController extends Controller
{
    /**
     * Threshold for async processing (items > this will use queue).
     */
    const ASYNC_THRESHOLD = 50;

    /**
     * Bulk ingest endpoint for external Python bots.
     * Automatically decides sync vs async based on data volume.
     * Auto-generates slugs if not provided.
     */
    public function store(Request $request)
    {
        Log::info('IngestController received input', [
            'all_input' => $request->all(),
            'method' => $request->method(),
            'content_type' => $request->header('Content-Type'),
        ]);

        $data = $request->validate([
            'taxonomies' => 'sometimes|array',
            'taxonomies.*.name' => 'required|string|max:255',
            'taxonomies.*.slug' => 'sometimes|string|max:255|regex:/^[a-z0-9-]+$/',
            'taxonomies.*.parent_slug' => 'sometimes|string|regex:/^[a-z0-9-]+$/',

            'locations' => 'sometimes|array',
            'locations.*.name' => 'required|string|max:255',
            'locations.*.slug' => 'sometimes|string|max:255|regex:/^[a-z0-9-]+$/',
            'locations.*.parent_slug' => 'sometimes|string|regex:/^[a-z0-9-]+$/',

            'content_nodes' => 'sometimes|array',
            'content_nodes.*.title' => 'required|string|max:255',
            'content_nodes.*.slug' => 'sometimes|string|max:255|regex:/^[a-z0-9-]+$/',
            'content_nodes.*.body_content' => 'required|string',
            'content_nodes.*.taxonomy_id' => 'sometimes|integer',
            'content_nodes.*.taxonomy_slug' => 'sometimes|string',
            'content_nodes.*.location_id' => 'sometimes|integer',
            'content_nodes.*.location_slug' => 'sometimes|string',
            'content_nodes.*.template_slug' => 'sometimes|string',
            'content_nodes.*.is_restricted_content' => 'sometimes|boolean',
            'content_nodes.*.publish_date' => 'sometimes|date',
            'content_nodes.*.published_at' => 'sometimes|date',
            'content_nodes.*.meta_description' => 'sometimes|string|max:320',
            'content_nodes.*.language' => 'sometimes|string|max:5',
            'content_nodes.*.source' => 'sometimes|string|max:50',

            'nodes' => 'sometimes|array',
            'nodes.*.title' => 'required|string|max:255',
            'nodes.*.slug' => 'sometimes|string|max:255|regex:/^[a-z0-9-]+$/',
            'nodes.*.body_content' => 'required|string',
            'nodes.*.taxonomy_id' => 'sometimes|integer',
            'nodes.*.taxonomy_slug' => 'sometimes|string',
            'nodes.*.location_id' => 'sometimes|integer',
            'nodes.*.location_slug' => 'sometimes|string',
            'nodes.*.template_slug' => 'sometimes|string',
            'nodes.*.is_restricted_content' => 'sometimes|boolean',
            'nodes.*.publish_date' => 'sometimes|date',
            'nodes.*.published_at' => 'sometimes|date',

            'live_data' => 'sometimes|array',
            'live_data.*.key' => 'required|string|max:255|regex:/^[a-z0-9_]+$/',
            'live_data.*.value' => 'required|string',
            'live_data.*.display_name' => 'sometimes|string|max:255',

            'post_templates' => 'sometimes|array',
            'post_templates.*.name' => 'required|string|max:255',
            'post_templates.*.slug' => 'sometimes|string|max:255|regex:/^[a-z0-9-]+$/',
            'post_templates.*.template_body' => 'required|string',
            'post_templates.*.taxonomy_slug' => 'sometimes|string',
        ]);

        if (!empty($data['nodes'])) {
            $data['content_nodes'] = $data['nodes'];
            unset($data['nodes']);
        }

        $totalItems = (
            count($data['taxonomies'] ?? []) +
            count($data['locations'] ?? []) +
            count($data['content_nodes'] ?? []) +
            count($data['live_data'] ?? []) +
            count($data['post_templates'] ?? [])
        );

        // Route to async queue for large datasets
        if ($totalItems > self::ASYNC_THRESHOLD) {
            return $this->dispatchAsync($data, $totalItems);
        }

        // Process synchronously for small datasets
        return $this->processSync($data);
    }

    /**
     * Dispatch to async queue job.
     */
    protected function dispatchAsync(array $data, int $totalItems): \Illuminate\Http\JsonResponse
    {
        Log::info("Dispatching bulk ingest job to queue", ['total_items' => $totalItems]);

        ProcessBulkIngestJob::dispatch($data);

        return response()->json([
            'success' => true,
            'message' => 'Bulk ingestion queued for async processing',
            'status' => 'processing',
            'estimated_time' => ceil($totalItems / 100) . ' minutes',
            'track_with' => 'POST /api/v1/ingest/status',
        ], 202);
    }

    /**
     * Synchronous processing for small datasets.
     */
    protected function processSync(array $data): \Illuminate\Http\JsonResponse
    {
        $results = [
            'taxonomies' => [],
            'locations' => [],
            'content_nodes' => [],
            'live_data' => [],
            'post_templates' => [],
            'errors' => [],
        ];

        // Process Taxonomies
        if (!empty($data['taxonomies'])) {
            foreach ($data['taxonomies'] as $tax) {
                try {
                    $parentId = null;
                    if (!empty($tax['parent_slug'])) {
                        $parent = Taxonomy::where('slug', $tax['parent_slug'])->first();
                        $parentId = $parent?->id;
                    }
                    $taxonomy = Taxonomy::updateOrCreate(
                        ['slug' => $tax['slug']],
                        ['name' => $tax['name'], 'parent_id' => $parentId]
                    );
                    $results['taxonomies'][] = ['slug' => $taxonomy->slug, 'id' => $taxonomy->id];
                } catch (\Exception $e) {
                    $results['errors'][] = ['taxonomy' => $tax['slug'], 'error' => $e->getMessage()];
                }
            }
        }

        // Process Locations
        if (!empty($data['locations'])) {
            foreach ($data['locations'] as $loc) {
                try {
                    $parentId = null;
                    if (!empty($loc['parent_slug'])) {
                        $parent = Location::where('slug', $loc['parent_slug'])->first();
                        $parentId = $parent?->id;
                    }
                    $location = Location::updateOrCreate(
                        ['slug' => $loc['slug']],
                        ['name' => $loc['name'], 'parent_id' => $parentId]
                    );
                    $results['locations'][] = ['slug' => $location->slug, 'id' => $location->id];
                } catch (\Exception $e) {
                    $results['errors'][] = ['location' => $loc['slug'], 'error' => $e->getMessage()];
                }
            }
        }

        // Process Post Templates
        if (!empty($data['post_templates'])) {
            foreach ($data['post_templates'] as $tmpl) {
                try {
                    $taxonomyId = null;
                    if (!empty($tmpl['taxonomy_slug'])) {
                        $tax = Taxonomy::where('slug', $tmpl['taxonomy_slug'])->first();
                        $taxonomyId = $tax?->id;
                    }
                    $template = PostTemplate::updateOrCreate(
                        ['slug' => $tmpl['slug']],
                        ['name' => $tmpl['name'], 'template_body' => $tmpl['template_body'], 'taxonomy_id' => $taxonomyId]
                    );
                    $results['post_templates'][] = ['slug' => $template->slug, 'id' => $template->id];
                } catch (\Exception $e) {
                    $results['errors'][] = ['template' => $tmpl['slug'], 'error' => $e->getMessage()];
                }
            }
        }

        // Process Live Data
        if (!empty($data['live_data'])) {
            foreach ($data['live_data'] as $ld) {
                try {
                    $vault = LiveDataVault::updateOrCreate(
                        ['key' => $ld['key']],
                        ['value' => $ld['value'], 'display_name' => $ld['display_name'] ?? null]
                    );
                    $results['live_data'][] = ['key' => $vault->key, 'id' => $vault->id];
                } catch (\Exception $e) {
                    $results['errors'][] = ['live_data' => $ld['key'], 'error' => $e->getMessage()];
                }
            }
        }

        // Process Content Nodes
        if (!empty($data['content_nodes'])) {
            foreach ($data['content_nodes'] as $node) {
                try {
                    if (!empty($node['taxonomy_id'])) {
                        $taxonomy = Taxonomy::find($node['taxonomy_id']);
                    } else {
                        $taxonomy = Taxonomy::firstOrCreate(
                            ['slug' => $node['taxonomy_slug']],
                            ['name' => Str::title(str_replace('-', ' ', $node['taxonomy_slug']))]
                        );
                    }

                    if (!empty($node['location_id'])) {
                        $location = Location::find($node['location_id']);
                    } elseif (!empty($node['location_slug'])) {
                        $location = Location::where('slug', $node['location_slug'])->firstOrFail();
                    } else {
                        // Default location if not provided
                        $location = Location::firstOrCreate(
                            ['slug' => 'default'],
                            ['name' => 'Default']
                        );
                    }

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
                        // Ensure unique even if provided
                        $slug = SlugGenerator::generateUnique($slug, $taxonomy->id, $location->id);
                    }

                    // Support both publish_date and published_at
                    $publishDate = $node['publish_date'] ?? $node['published_at'] ?? now();

                    $contentNode = ContentNode::updateOrCreate(
                        ['slug' => $slug, 'taxonomy_id' => $taxonomy->id, 'location_id' => $location->id],
                        [
                            'uuid' => $node['uuid'] ?? Str::uuid()->toString(),
                            'seo_title' => $node['title'],
                            'body_content' => $node['body_content'],
                            'post_template_id' => $templateId,
                            'meta_description' => $node['meta_description'] ?? null,
                            'featured_image' => $node['featured_image'] ?? null,
                            'is_restricted_content' => $node['is_restricted_content'] ?? false,
                            'language' => $node['language'] ?? config('app.locale', 'tr'),
                            'source' => $node['source'] ?? 'api',
                            'publish_date' => $publishDate,
                        ]
                    );

                    AnalyzeContentQualityJob::dispatch($contentNode);

                    $results['content_nodes'][] = ['slug' => $contentNode->slug, 'id' => $contentNode->id, 'auto_generated_slug' => empty($node['slug'])];
                } catch (\Exception $e) {
                    $results['errors'][] = ['content_node' => $node['title'] ?? 'unknown', 'error' => $e->getMessage()];
                }
            }
        }

        Log::info('Bulk ingest completed (sync)', ['results' => $results]);

        return response()->json([
            'success' => true,
            'message' => 'Bulk ingestion completed',
            'results' => $results,
        ], 200);
    }

    /**
     * Health check endpoint.
     */
    public function health()
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
            'version' => '1.0.0',
            'queue_driver' => config('queue.default'),
        ]);
    }

    /**
     * Queue status endpoint.
     */
    public function status()
    {
        $pending = \App\Models\ContentNode::count();
        $taxonomies = \App\Models\Taxonomy::count();
        $locations = \App\Models\Location::count();

        return response()->json([
            'status' => 'ok',
            'totals' => [
                'content_nodes' => $pending,
                'taxonomies' => $taxonomies,
                'locations' => $locations,
            ],
            'queue' => [
                'driver' => config('queue.default'),
                'connection' => config('queue.connections.' . config('queue.default') . '.driver'),
            ],
        ]);
    }
}