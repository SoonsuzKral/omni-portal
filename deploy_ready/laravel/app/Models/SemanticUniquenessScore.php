<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SemanticUniquenessScore extends Model
{
    protected $table = 'semantic_uniqueness_scores';

    protected $fillable = [
        'content_node_id',
        'semantic_similarity_score',
        'sentence_entropy_score',
        'lexical_diversity_score',
        'template_saturation_score',
        'embedding_uniqueness_score',
        'heading_duplication_score',
        'overall_uniqueness_score',
        'similar_pages',
        'analysis_details',
    ];

    protected $casts = [
        'semantic_similarity_score' => 'decimal:2',
        'sentence_entropy_score' => 'decimal:2',
        'lexical_diversity_score' => 'decimal:2',
        'template_saturation_score' => 'decimal:2',
        'embedding_uniqueness_score' => 'decimal:2',
        'heading_duplication_score' => 'decimal:2',
        'overall_uniqueness_score' => 'decimal:2',
        'similar_pages' => 'array',
        'analysis_details' => 'array',
    ];

    public function contentNode(): BelongsTo
    {
        return $this->belongsTo(ContentNode::class);
    }

    public function scopeLowUniqueness($query, float $threshold = 40)
    {
        return $query->where('overall_uniqueness_score', '<', $threshold);
    }

    public function scopeHighUniqueness($query, float $threshold = 80)
    {
        return $query->where('overall_uniqueness_score', '>=', $threshold);
    }
}
