<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EeatSignal extends Model
{
    protected $table = 'eeat_signals';

    protected $fillable = [
        'content_node_id',
        'signal_type',
        'signal_score',
        'signal_evidence',
        'signal_details',
    ];

    protected $casts = [
        'signal_score' => 'decimal:2',
        'signal_details' => 'array',
    ];

    public function contentNode(): BelongsTo
    {
        return $this->belongsTo(ContentNode::class);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('signal_type', $type);
    }

    public function scopeHighSignal($query, float $threshold = 70)
    {
        return $query->where('signal_score', '>=', $threshold);
    }

    public function scopeLowSignal($query, float $threshold = 40)
    {
        return $query->where('signal_score', '<', $threshold);
    }
}
