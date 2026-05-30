<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnomalyDetection extends Model
{
    protected $table = 'anomaly_detections';

    protected $fillable = [
        'content_node_id',
        'url',
        'anomaly_type',
        'severity',
        'current_value',
        'previous_value',
        'threshold',
        'deviation',
        'description',
        'context',
        'detected_at',
        'resolved_at',
        'is_active',
    ];

    protected $casts = [
        'current_value' => 'decimal:4',
        'previous_value' => 'decimal:4',
        'threshold' => 'decimal:4',
        'deviation' => 'decimal:4',
        'context' => 'array',
        'detected_at' => 'datetime',
        'resolved_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function contentNode(): BelongsTo
    {
        return $this->belongsTo(ContentNode::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('anomaly_type', $type);
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    public function scopeWarning($query)
    {
        return $query->where('severity', 'warning');
    }

    public function scopeInfo($query)
    {
        return $query->where('severity', 'info');
    }
}
