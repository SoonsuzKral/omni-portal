<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EntityAuthorityGraph extends Model
{
    protected $table = 'entity_authority_graph';

    protected $fillable = [
        'entity_type',
        'entity_name',
        'entity_slug',
        'description',
        'entity_authority_score',
        'topical_relevance_score',
        'inbound_link_count',
        'outbound_link_count',
        'mention_count',
        'metadata',
    ];

    protected $casts = [
        'entity_authority_score' => 'decimal:2',
        'topical_relevance_score' => 'decimal:2',
        'inbound_link_count' => 'integer',
        'outbound_link_count' => 'integer',
        'mention_count' => 'integer',
        'metadata' => 'array',
    ];

    public function sourceRelationships(): HasMany
    {
        return $this->hasMany(EntityRelationship::class, 'source_entity_id');
    }

    public function targetRelationships(): HasMany
    {
        return $this->hasMany(EntityRelationship::class, 'target_entity_id');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('entity_type', $type);
    }

    public function scopeHighAuthority($query, float $threshold = 70)
    {
        return $query->where('entity_authority_score', '>=', $threshold);
    }

    public function scopeHighRelevance($query, float $threshold = 70)
    {
        return $query->where('topical_relevance_score', '>=', $threshold);
    }
}
