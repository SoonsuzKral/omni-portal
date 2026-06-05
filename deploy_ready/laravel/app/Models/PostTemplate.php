<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostTemplate extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'taxonomy_id',
        'template_body',
    ];

    public function taxonomy()
    {
        return $this->belongsTo(Taxonomy::class);
    }

    public function contentNodes()
    {
        return $this->hasMany(ContentNode::class);
    }
}
