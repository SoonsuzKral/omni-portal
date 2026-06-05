<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaxonomySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample taxonomy hierarchy
        \App\Models\Taxonomy::create(['name' => 'Health', 'slug' => 'health', 'parent_id' => null]);
        \App\Models\Taxonomy::create(['name' => 'Nutrition', 'slug' => 'nutrition', 'parent_id' => 1]);
        \App\Models\Taxonomy::create(['name' => 'Fitness', 'slug' => 'fitness', 'parent_id' => 1]);
    }
}
