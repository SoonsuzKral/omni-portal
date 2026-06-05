<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class TopicAuthorityScore extends Model
{
    protected $table = 'topic_authority_scores';

    protected $fillable = [
        'topicable_type',
        'topicable_id',
        'topic_coverage_score',
        'entity_completeness_score',
        'semantic_cluster_depth',
        'supporting_content_ratio',
        'internal_topical_links_score',
        'authority_cluster_score',
        'cluster_members',
        'analysis_details',
    ];

    protected $casts = [
        'topic_coverage_score' => 'decimal:2',
        'entity_completeness_score' => 'decimal:2',
        'semantic_cluster_depth' => 'decimal:2',
        'supporting_content_ratio' => 'decimal:2',
        'internal_topical_links_score' => 'decimal:2',
        'authority_cluster_score' => 'decimal:2',
        'cluster_members' => 'array',
        'analysis_details' => 'array',
    ];

    public function topicable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeByTopicable($query, string $type, int $id)
    {
        return $query->where('topicable_type', $type)->where('topicable_id', $id);
    }

    public function scopeHighAuthority($query, float $threshold = 70)
    {
        return $query->where('authority_cluster_score', '>=', $threshold);
    }

    public function scopeHighCoverage($query, float $threshold = 70)
    {
        return $query->where('topic_coverage_score', '>=', $threshold);
    }
}
