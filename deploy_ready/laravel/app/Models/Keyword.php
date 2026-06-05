<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keyword extends Model
{
    protected $fillable = [
        'keyword',
        'slug',
        'language',
        'search_volume',
        'difficulty',
        'category_id',
        'location_id',
        'is_trending',
        'is_auto_generated',
        'clicks',
        'impressions',
        'position',
    ];

    protected $casts = [
        'is_trending' => 'boolean',
        'is_auto_generated' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Taxonomy::class, 'category_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function contentNodes()
    {
        return $this->hasMany(ContentNode::class, 'slug', 'slug');
    }

    /**
     * Get the mapped content node for this keyword.
     */
    public function getMappedContentAttribute()
    {
        return ContentNode::where('slug', $this->slug)->first();
    }

    public function scopeByLanguage($query, $lang)
    {
        return $query->where('language', $lang);
    }

    public function scopeTrending($query)
    {
        return $query->where('is_trending', true)->orderBy('search_volume', 'desc');
    }

    public function scopePopular($query)
    {
        return $query->orderBy('clicks', 'desc')->limit(50);
    }
}