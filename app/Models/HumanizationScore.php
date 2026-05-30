<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HumanizationScore extends Model
{
    protected $table = 'humanization_scores';

    protected $fillable = [
        'content_node_id',
        'sentence_rhythm_score',
        'structure_variation_score',
        'paragraph_diversity_score',
        'narrative_variation_score',
        'tone_adaptation_score',
        'overall_humanization_score',
        'analysis_details',
    ];

    protected $casts = [
        'sentence_rhythm_score' => 'decimal:2',
        'structure_variation_score' => 'decimal:2',
        'paragraph_diversity_score' => 'decimal:2',
        'narrative_variation_score' => 'decimal:2',
        'tone_adaptation_score' => 'decimal:2',
        'overall_humanization_score' => 'decimal:2',
        'analysis_details' => 'array',
    ];

    public function contentNode(): BelongsTo
    {
        return $this->belongsTo(ContentNode::class);
    }

    public function scopeLowHumanization($query, float $threshold = 40)
    {
        return $query->where('overall_humanization_score', '<', $threshold);
    }

    public function scopeHighHumanization($query, float $threshold = 75)
    {
        return $query->where('overall_humanization_score', '>=', $threshold);
    }
}
