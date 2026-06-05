<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LiveDataVaultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample live data entries
        \App\Models\LiveDataVault::create(['key' => 'usd_try', 'value' => '8.50']);
        \App\Models\LiveDataVault::create(['key' => 'gold_price', 'value' => '1900']);
        \App\Models\LiveDataVault::create(['key' => 'weather_istanbul', 'value' => 'sunny']);
    }
}
