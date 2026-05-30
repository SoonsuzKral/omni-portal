<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class GlobalAdBlock extends Model
{
    protected $fillable = ['name', 'script', 'network_type', 'active', 'position', 'forbidden_locations', 'cpm_note', 'taxonomy_id', 'is_global'];

    protected $casts = [
        'forbidden_locations' => 'array',
        'active' => 'boolean',
        'is_global' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::flush());
        static::deleted(fn () => Cache::flush());
    }

    public function taxonomy(): BelongsTo
    {
        return $this->belongsTo(Taxonomy::class);
    }
}
?>