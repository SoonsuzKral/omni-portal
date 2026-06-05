<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class LiveDataVault extends Model
{
    protected $fillable = ['key', 'value', 'data_type'];

    protected static function booted(): void
    {
        static::saved(function ($record) {
            if (in_array($record->key, ['ads_test_mode', 'adsense_verification_enabled', 'adsense_enabled'])) {
                Cache::flush();
            }
        });
        static::deleted(function ($record) {
            if (in_array($record->key, ['ads_test_mode', 'adsense_verification_enabled', 'adsense_enabled'])) {
                Cache::flush();
            }
        });
    }
}
