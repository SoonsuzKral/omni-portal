<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SitemapRefreshJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 120;

    public function handle(): void
    {
        Log::info('Sitemap cache refresh triggered');

        // Clear sitemap-related caches
        Cache::forget('sitemap_content_nodes');
        Cache::forget('sitemap_taxonomies');
        Cache::forget('sitemap_locations');

        // Pre-warm sitemap data cache for faster generation
        $this->prewarmSitemapCache();

        Log::info('Sitemap cache refreshed successfully');
    }

    protected function prewarmSitemapCache(): void
    {
        $content = \App\Models\ContentNode::whereNotNull('publish_date')
            ->select(['id', 'slug', 'taxonomy_id', 'location_id', 'updated_at'])
            ->orderBy('updated_at', 'desc')
            ->limit(50000)
            ->get();

        Cache::put('sitemap_content_nodes', $content->toArray(), now()->addHours(24));

        $taxonomies = \App\Models\Taxonomy::select(['id', 'slug', 'updated_at'])->get();
        Cache::put('sitemap_taxonomies', $taxonomies->toArray(), now()->addHours(24));

        $locations = \App\Models\Location::select(['id', 'slug', 'parent_id', 'updated_at'])->get();
        Cache::put('sitemap_locations', $locations->toArray(), now()->addHours(24));
    }
}