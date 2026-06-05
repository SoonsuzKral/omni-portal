<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSatisfactionScore extends Model
{
    protected $table = 'user_satisfaction_scores';

    protected $fillable = [
        'content_node_id',
        'dwell_time_score',
        'scroll_depth_score',
        'interaction_rate_score',
        'bounce_behavior_score',
        'cta_engagement_score',
        'navigation_depth_score',
        'engagement_quality_score',
        'satisfaction_score',
        'raw_metrics',
        'analysis_details',
    ];

    protected $casts = [
        'dwell_time_score' => 'decimal:2',
        'scroll_depth_score' => 'decimal:2',
        'interaction_rate_score' => 'decimal:2',
        'bounce_behavior_score' => 'decimal:2',
        'cta_engagement_score' => 'decimal:2',
        'navigation_depth_score' => 'decimal:2',
        'engagement_quality_score' => 'decimal:2',
        'satisfaction_score' => 'decimal:2',
        'raw_metrics' => 'array',
        'analysis_details' => 'array',
    ];

    public function contentNode(): BelongsTo
    {
        return $this->belongsTo(ContentNode::class);
    }

    public function scopeHighSatisfaction($query, float $threshold = 70)
    {
        return $query->where('satisfaction_score', '>=', $threshold);
    }

    public function scopeLowSatisfaction($query, float $threshold = 40)
    {
        return $query->where('satisfaction_score', '<', $threshold);
    }

    public function scopeHighEngagement($query, float $threshold = 70)
    {
        return $query->where('engagement_quality_score', '>=', $threshold);
    }
}
