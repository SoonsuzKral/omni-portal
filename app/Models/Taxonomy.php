<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Taxonomy extends Model
{
    protected $fillable = ['name', 'slug', 'parent_id', 'locale', 'is_active'];

    protected $attributes = [
        'is_active' => true,
    ];

    public function parent()
    {
        return $this->belongsTo(Taxonomy::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Taxonomy::class, 'parent_id');
    }

    public function contentNodes()
    {
        return $this->hasMany(ContentNode::class);
    }
}
