<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AntiSpamRiskScore extends Model
{
    protected $table = 'anti_spam_risk_scores';

    protected $fillable = [
        'content_node_id',
        'scaled_content_abuse_score',
        'template_overuse_score',
        'semantic_redundancy_score',
        'doorway_page_risk_score',
        'thin_content_risk_score',
        'over_optimization_score',
        'overall_spam_risk_score',
        'risk_factors',
        'analysis_details',
    ];

    protected $casts = [
        'scaled_content_abuse_score' => 'decimal:2',
        'template_overuse_score' => 'decimal:2',
        'semantic_redundancy_score' => 'decimal:2',
        'doorway_page_risk_score' => 'decimal:2',
        'thin_content_risk_score' => 'decimal:2',
        'over_optimization_score' => 'decimal:2',
        'overall_spam_risk_score' => 'decimal:2',
        'risk_factors' => 'array',
        'analysis_details' => 'array',
    ];

    public function contentNode(): BelongsTo
    {
        return $this->belongsTo(ContentNode::class);
    }

    public function scopeHighRisk($query, float $threshold = 60)
    {
        return $query->where('overall_spam_risk_score', '>=', $threshold);
    }

    public function scopeLowRisk($query, float $threshold = 30)
    {
        return $query->where('overall_spam_risk_score', '<', $threshold);
    }

    public function scopeDoorwayRisk($query, float $threshold = 50)
    {
        return $query->where('doorway_page_risk_score', '>=', $threshold);
    }

    public function scopeTemplateAbuse($query, float $threshold = 60)
    {
        return $query->where('template_overuse_score', '>=', $threshold);
    }
}
