<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample location hierarchy
        \App\Models\Location::create(['name' => 'Turkey', 'slug' => 'turkey', 'parent_id' => null]);
        \App\Models\Location::create(['name' => 'Istanbul', 'slug' => 'istanbul', 'parent_id' => 1]);
        \App\Models\Location::create(['name' => 'Kadikoy', 'slug' => 'kadikoy', 'parent_id' => 2]);
    }
}
