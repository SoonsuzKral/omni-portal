<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = ['name', 'slug', 'parent_id', 'locale'];

    protected $casts = [
        'parent_id' => 'integer',
    ];

    public function parent()
    {
        return $this->belongsTo(Location::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Location::class, 'parent_id');
    }

    public function contentNodes()
    {
        return $this->hasMany(ContentNode::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($location) {
            if (!static::where('slug', $location->slug)->where('id', '!=', $location->id)->exists()) {
                return;
            }
            $location->slug = $location->slug . '-' . $location->id;
            $location->save();
        });
    }
}
