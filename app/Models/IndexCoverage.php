<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndexCoverage extends Model
{
    protected $table = 'index_coverage';

    protected $fillable = [
        'snapshot_date',
        'submitted_urls',
        'indexed_urls',
        'coverage_ratio',
        'avg_crawl_latency_seconds',
        'indexing_velocity',
        'sitemap_count',
        'sitemap_indexed',
        'sitemap_efficiency',
        'crawl_requests',
        'crawl_errors',
        'breakdown',
    ];

    protected $casts = [
        'snapshot_date' => 'date:Y-m-d',
        'submitted_urls' => 'integer',
        'indexed_urls' => 'integer',
        'coverage_ratio' => 'decimal:4',
        'avg_crawl_latency_seconds' => 'decimal:2',
        'indexing_velocity' => 'decimal:2',
        'sitemap_count' => 'integer',
        'sitemap_indexed' => 'integer',
        'sitemap_efficiency' => 'decimal:4',
        'crawl_requests' => 'integer',
        'crawl_errors' => 'integer',
        'breakdown' => 'array',
    ];

    public function scopeLatest($query)
    {
        return $query->orderBy('snapshot_date', 'desc')->limit(1);
    }
}
