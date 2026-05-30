<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveDataVault extends Model
{
    protected $fillable = ['key', 'value', 'data_type'];
}
