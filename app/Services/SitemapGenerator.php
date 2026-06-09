<?php

namespace App\Services;

use App\Models\ContentNode;
use App\Models\Taxonomy;
use App\Models\Location;
use App\Http\Controllers\ContentController;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use XMLWriter;

class SitemapGenerator
{
    const MAX_URLS_PER_SHARD = 50000;
    const CACHE_TTL = 3600;
    const SITEMAP_NS = 'http://www.sitemaps.org/schemas/sitemap/0.9';

    public function generateIndex(): string
    {
        return Cache::remember('sitemap_index_v2', self::CACHE_TTL, function () {
            return $this->buildIndexXml();
        });
    }

    public function generateContentShard(int $page): ?string
    {
        $cacheKey = "sitemap_shard_v2_{$page}";
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($page) {
            return $this->buildContentShardXml($page);
        });
    }

    public function generateCategories(): string
    {
        return Cache::remember('sitemap_categories_v2', self::CACHE_TTL, function () {
            return $this->buildCategoriesXml();
        });
    }

    public function generateLocations(): string
    {
        return Cache::remember('sitemap_locations_v2', self::CACHE_TTL, function () {
            return $this->buildLocationsXml();
        });
    }

    public function getTotalShards(): int
    {
        $total = $this->getIndexedContentQuery()->count();
        return max(1, (int) ceil($total / self::MAX_URLS_PER_SHARD));
    }

    protected function getIndexedContentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $indexedCities = ContentController::INDEXED_CITIES;
        $indexedCategories = ContentController::INDEXED_CATEGORIES;

        return ContentNode::whereNotNull('publish_date')
            ->whereHas('location', fn($q) => $q->whereIn('slug', $indexedCities))
            ->whereHas('taxonomy', fn($q) => $q->whereIn('slug', $indexedCategories));
    }

    public function invalidateCache(): void
    {
        Cache::forget('sitemap_index_v2');
        $totalShards = $this->getTotalShards();
        for ($i = 1; $i <= $totalShards; $i++) {
            Cache::forget("sitemap_shard_v2_{$i}");
        }
        Cache::forget('sitemap_categories_v2');
        Cache::forget('sitemap_locations_v2');
    }

    protected function buildIndexXml(): string
    {
        $writer = new XMLWriter();
        $writer->openMemory();
        $writer->setIndent(true);
        $writer->startDocument('1.0', 'UTF-8');
        $writer->startElement('sitemapindex');
        $writer->writeAttribute('xmlns', self::SITEMAP_NS);

        $totalShards = $this->getTotalShards();
        for ($i = 1; $i <= $totalShards; $i++) {
            $lastmod = $this->getShardLastmod($i);
            $this->writeSitemapRef($writer, url("/sitemap-content-{$i}.xml"), $lastmod);
        }

        $categoriesLastmod = Taxonomy::max('updated_at');
        $this->writeSitemapRef($writer, url('/sitemap-categories.xml'), $categoriesLastmod);

        $locationsLastmod = Location::max('updated_at');
        $this->writeSitemapRef($writer, url('/sitemap-locations.xml'), $locationsLastmod);

        $writer->endElement();
        $writer->endDocument();
        return $writer->outputMemory();
    }

    protected function buildContentShardXml(int $page): ?string
    {
        $offset = ($page - 1) * self::MAX_URLS_PER_SHARD;

        $writer = new XMLWriter();
        $writer->openMemory();
        $writer->setIndent(true);
        $writer->startDocument('1.0', 'UTF-8');
        $writer->startElement('urlset');
        $writer->writeAttribute('xmlns', self::SITEMAP_NS);

        $added = 0;
        $this->getIndexedContentQuery()
            ->orderBy('crawl_priority_score', 'desc')
            ->orderBy('updated_at', 'desc')
            ->select('id', 'slug', 'taxonomy_id', 'location_id', 'updated_at', 'crawl_priority_score')
            ->with(['taxonomy:id,slug', 'location:id,slug'])
            ->skip($offset)
            ->limit(self::MAX_URLS_PER_SHARD)
            ->chunk(1000, function ($nodes) use ($writer, &$added) {
                foreach ($nodes as $node) {
                    $taxSlug = $node->taxonomy?->slug ?? '';
                    $locSlug = $node->location?->slug ?? '';
                    $url = url("/{$taxSlug}/{$locSlug}/{$node->slug}");
                    $priority = $this->priorityFromScore($node->crawl_priority_score);
                    $this->writeUrl($writer, $url, $node->updated_at, 'monthly', $priority);
                    $added++;
                }
            });

        if ($added === 0) {
            return null;
        }

        $writer->endElement();
        $writer->endDocument();
        return $writer->outputMemory();
    }

    protected function buildCategoriesXml(): string
    {
        $writer = new XMLWriter();
        $writer->openMemory();
        $writer->setIndent(true);
        $writer->startDocument('1.0', 'UTF-8');
        $writer->startElement('urlset');
        $writer->writeAttribute('xmlns', self::SITEMAP_NS);

        $this->writeUrl($writer, url('/categories'), now(), 'daily', '0.9');

        Taxonomy::select('slug', 'updated_at')
            ->orderBy('slug')
            ->chunk(500, function ($taxonomies) use ($writer) {
                foreach ($taxonomies as $tax) {
                    $this->writeUrl($writer, url("/{$tax->slug}"), $tax->updated_at, 'weekly', '0.8');
                }
            });

        $writer->endElement();
        $writer->endDocument();
        return $writer->outputMemory();
    }

    protected function buildLocationsXml(): string
    {
        $writer = new XMLWriter();
        $writer->openMemory();
        $writer->setIndent(true);
        $writer->startDocument('1.0', 'UTF-8');
        $writer->startElement('urlset');
        $writer->writeAttribute('xmlns', self::SITEMAP_NS);

        $this->writeUrl($writer, url('/locations'), now(), 'daily', '0.9');

        Location::select('slug', 'updated_at')
            ->orderBy('slug')
            ->chunk(500, function ($locations) use ($writer) {
                foreach ($locations as $loc) {
                    $this->writeUrl($writer, url("/location/{$loc->slug}"), $loc->updated_at, 'weekly', '0.7');
                }
            });

        $writer->endElement();
        $writer->endDocument();
        return $writer->outputMemory();
    }

    protected function getShardLastmod(int $page): ?string
    {
        $offset = ($page - 1) * self::MAX_URLS_PER_SHARD;
        $latest = $this->getIndexedContentQuery()
            ->orderBy('crawl_priority_score', 'desc')
            ->orderBy('updated_at', 'desc')
            ->skip($offset)
            ->limit(1)
            ->value('updated_at');

        return $latest ? Carbon::parse($latest)->toIso8601String() : now()->toIso8601String();
    }

    protected function writeSitemapRef(XMLWriter $writer, string $loc, mixed $lastmod): void
    {
        $writer->startElement('sitemap');
        $writer->writeElement('loc', $loc);
        $writer->writeElement('lastmod', $lastmod
            ? Carbon::parse($lastmod)->toIso8601String()
            : now()->toIso8601String());
        $writer->endElement();
    }

    protected function writeUrl(XMLWriter $writer, string $loc, mixed $lastmod, string $changefreq, string $priority): void
    {
        $lastmodStr = $lastmod
            ? Carbon::parse($lastmod)->toIso8601String()
            : now()->toIso8601String();

        $writer->startElement('url');
        $writer->writeElement('loc', $loc);
        $writer->writeElement('lastmod', $lastmodStr);
        $writer->writeElement('changefreq', $changefreq);
        $writer->writeElement('priority', $priority);
        $writer->endElement();
    }

    protected function priorityFromScore(?float $score): string
    {
        if ($score === null) return '0.5';
        if ($score >= 80) return '0.9';
        if ($score >= 60) return '0.8';
        if ($score >= 40) return '0.7';
        if ($score >= 20) return '0.6';
        return '0.5';
    }
}
