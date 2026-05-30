<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntityRelationship extends Model
{
    protected $fillable = [
        'source_entity_id',
        'target_entity_id',
        'relationship_type',
        'relationship_strength',
        'context',
    ];

    protected $casts = [
        'relationship_strength' => 'decimal:2',
    ];

    public function source(): BelongsTo
    {
        return $this->belongsTo(EntityAuthorityGraph::class, 'source_entity_id');
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(EntityAuthorityGraph::class, 'target_entity_id');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('relationship_type', $type);
    }

    public function scopeStrong($query, float $threshold = 50)
    {
        return $query->where('relationship_strength', '>=', $threshold);
    }
}
