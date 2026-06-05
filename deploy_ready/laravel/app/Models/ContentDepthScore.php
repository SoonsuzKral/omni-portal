<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentDepthScore extends Model
{
    protected $table = 'content_depth_scores';

    protected $fillable = [
        'content_node_id',
        'depth_score',
        'richness_score',
        'faq_count',
        'semantic_expansion_count',
        'related_entity_count',
        'supporting_data_blocks',
        'comparison_sections',
        'enrichment_suggestions',
        'analysis_details',
    ];

    protected $casts = [
        'depth_score' => 'decimal:2',
        'richness_score' => 'decimal:2',
        'faq_count' => 'integer',
        'semantic_expansion_count' => 'integer',
        'related_entity_count' => 'integer',
        'supporting_data_blocks' => 'integer',
        'comparison_sections' => 'integer',
        'enrichment_suggestions' => 'array',
        'analysis_details' => 'array',
    ];

    public function contentNode(): BelongsTo
    {
        return $this->belongsTo(ContentNode::class);
    }

    public function scopeShallow($query, float $threshold = 40)
    {
        return $query->where('depth_score', '<', $threshold);
    }

    public function scopeDeep($query, float $threshold = 75)
    {
        return $query->where('depth_score', '>=', $threshold);
    }

    public function scopeRich($query, float $threshold = 70)
    {
        return $query->where('richness_score', '>=', $threshold);
    }
}
