<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentNode extends Model
{
    protected $fillable = [
        'uuid',
        'post_template_id',
        'taxonomy_id',
        'location_id',
        'seo_title',
        'slug',
        'body_content',
        'meta_description',
        'featured_image',
        'is_restricted_content',
        'ads_enabled',
        'page_views',
        'crawl_priority_score',
        'crawl_priority_breakdown',
        'publish_date',
        'locale',
        'language',
        'source',
        'gsc_first_discovered_at',
        'gsc_first_crawled_at',
        'gsc_first_indexed_at',
        'gsc_last_impression_at',
        'gsc_last_click_at',
        'gsc_avg_position',
        'gsc_total_impressions',
        'gsc_total_clicks',
        'gsc_index_status',
        'gsc_index_latency_minutes',
        'gsc_last_synced_at',
    ];

    protected $appends = ['title'];

    protected $casts = [
        'is_restricted_content' => 'boolean',
        'ads_enabled' => 'boolean',
        'publish_date' => 'datetime',
        'page_views' => 'integer',
        'crawl_priority_score' => 'decimal:2',
        'crawl_priority_breakdown' => 'array',
        'gsc_first_discovered_at' => 'datetime',
        'gsc_first_crawled_at' => 'datetime',
        'gsc_first_indexed_at' => 'datetime',
        'gsc_last_impression_at' => 'datetime',
        'gsc_last_click_at' => 'datetime',
        'gsc_avg_position' => 'decimal:2',
        'gsc_total_impressions' => 'integer',
        'gsc_total_clicks' => 'integer',
        'gsc_index_latency_minutes' => 'integer',
        'gsc_last_synced_at' => 'datetime',
    ];

    /**
     * Accessor for title (alias for seo_title).
     */
    public function getTitleAttribute(): ?string
    {
        return $this->seo_title ?? $this->attributes['seo_title'] ?? null;
    }

    public function getStatusAttribute(): string
    {
        return $this->publish_date ? 'published' : 'draft';
    }

    /**
     * Scope for restricted content filtering.
     */
    public function scopeRestricted($query)
    {
        return $query->where('is_restricted_content', true);
    }

    /**
     * Scope for non-restricted content.
     */
    public function scopeNonRestricted($query)
    {
        return $query->where('is_restricted_content', false);
    }

    /**
     * Scope: highest priority first.
     */
    public function scopeByPriority($query, string $direction = 'desc')
    {
        return $query->orderBy('crawl_priority_score', $direction);
    }

    /**
     * Scope: only content with score >= threshold.
     */
    public function scopeHighPriority($query, float $threshold = 70)
    {
        return $query->where('crawl_priority_score', '>=', $threshold);
    }

    /**
     * Scope: content within a score range.
     */
    public function scopePriorityRange($query, float $min, float $max)
    {
        return $query->whereBetween('crawl_priority_score', [$min, $max]);
    }

    /**
     * Scope: unscored content.
     */
    public function scopeUnscored($query)
    {
        return $query->whereNull('crawl_priority_score');
    }

    public function taxonomy()
    {
        return $this->belongsTo(Taxonomy::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function postTemplate()
    {
        return $this->belongsTo(PostTemplate::class);
    }

    /**
     * Get related content nodes (same taxonomy, different location).
     */
    public function telemetry()
    {
        return $this->hasMany(SearchConsoleTelemetry::class);
    }

    public function latestTelemetry()
    {
        return $this->hasOne(SearchConsoleTelemetry::class)->latest('date');
    }

    public function anomalies()
    {
        return $this->hasMany(AnomalyDetection::class);
    }

    public function activeAnomalies()
    {
        return $this->hasMany(AnomalyDetection::class)->where('is_active', true);
    }

    public function scopeIndexed($query)
    {
        return $query->where('gsc_index_status', 'INDEXED');
    }

    public function scopeNotIndexed($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('gsc_index_status')
              ->orWhere('gsc_index_status', '!=', 'INDEXED');
        });
    }

    public function scopeGscSynced($query)
    {
        return $query->whereNotNull('gsc_last_synced_at');
    }

    public function scopeGscNeverSynced($query)
    {
        return $query->whereNull('gsc_last_synced_at');
    }

    public function scopeHighCtr($query, float $threshold = 5.0)
    {
        return $query->where('gsc_total_impressions', '>', 0)
            ->whereRaw('(gsc_total_clicks / gsc_total_impressions * 100) >= ?', [$threshold]);
    }

    public function scopeLosingImpressions($query, int $days = 30)
    {
        $threshold = now()->subDays($days);
        return $query->whereNotNull('gsc_last_impression_at')
            ->where('gsc_last_impression_at', '<', $threshold)
            ->where('gsc_total_impressions', '>', 0);
    }

    public function scopeNeverIndexed($query)
    {
        return $query->whereNull('gsc_first_indexed_at');
    }

    public function relatedNodes()
    {
        return $this->hasMany(ContentNode::class, 'taxonomy_id', 'taxonomy_id')
            ->where('id', '!=', $this->id)
            ->orderBy('page_views', 'desc')
            ->limit(6);
    }

    public function semanticUniqueness()
    {
        return $this->hasOne(SemanticUniquenessScore::class);
    }

    public function eeatSignals()
    {
        return $this->hasMany(EeatSignal::class);
    }

    public function humanization()
    {
        return $this->hasOne(HumanizationScore::class);
    }

    public function topicAuthority()
    {
        return $this->morphOne(TopicAuthorityScore::class, 'topicable');
    }

    public function satisfaction()
    {
        return $this->hasOne(UserSatisfactionScore::class);
    }

    public function depth()
    {
        return $this->hasOne(ContentDepthScore::class);
    }

    public function spamRisk()
    {
        return $this->hasOne(AntiSpamRiskScore::class);
    }

    public function getAdScripts(string $position): array
    {
        if ($this->is_restricted_content) {
            return [];
        }

        $scripts = GlobalAdBlock::where('active', true)
            ->where('position', $position)
            ->pluck('script')
            ->toArray();

        return array_filter($scripts);
    }
}
