<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchConsoleTelemetry extends Model
{
    protected $table = 'search_console_telemetry';

    protected $fillable = [
        'content_node_id',
        'url',
        'date',
        'impressions',
        'clicks',
        'ctr',
        'avg_position',
        'first_discovered_at',
        'first_crawled_at',
        'first_indexed_at',
        'last_impression_at',
        'last_click_at',
        'index_status',
        'index_latency_minutes',
        'source',
        'raw_payload',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'impressions' => 'integer',
        'clicks' => 'integer',
        'ctr' => 'decimal:6',
        'avg_position' => 'decimal:2',
        'first_discovered_at' => 'datetime',
        'first_crawled_at' => 'datetime',
        'first_indexed_at' => 'datetime',
        'last_impression_at' => 'datetime',
        'last_click_at' => 'datetime',
        'index_latency_minutes' => 'integer',
        'raw_payload' => 'array',
    ];

    public function contentNode(): BelongsTo
    {
        return $this->belongsTo(ContentNode::class);
    }

    public function scopeIndexed($query)
    {
        return $query->where('index_status', 'INDEXED');
    }

    public function scopeNotIndexed($query)
    {
        return $query->where('index_status', '!=', 'INDEXED');
    }

    public function scopeDateRange($query, $start, $end)
    {
        return $query->whereBetween('date', [$start, $end]);
    }
}
