<?php

namespace Tests\Unit\Models;

use App\Models\Location;
use App\Models\Taxonomy;
use App\Models\ContentNode;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_location(): void
    {
        $location = Location::create([
            'name' => 'İstanbul',
            'slug' => 'istanbul',
        ]);

        $this->assertDatabaseHas('locations', [
            'name' => 'İstanbul',
            'slug' => 'istanbul',
        ]);
    }

    public function test_can_create_child_location(): void
    {
        $city = Location::create(['name' => 'İstanbul', 'slug' => 'istanbul']);
        $district = Location::create(['name' => 'Kadıköy', 'slug' => 'kadikoy', 'parent_id' => $city->id]);

        $this->assertEquals($city->id, $district->parent->id);
    }

    public function test_can_get_children(): void
    {
        $city = Location::create(['name' => 'İstanbul', 'slug' => 'istanbul']);
        Location::create(['name' => 'Kadıköy', 'slug' => 'kadikoy', 'parent_id' => $city->id]);
        Location::create(['name' => 'Beşiktaş', 'slug' => 'besiktas', 'parent_id' => $city->id]);

        $this->assertCount(2, $city->children);
    }

    public function test_can_get_content_nodes(): void
    {
        $location = Location::create(['name' => 'İstanbul', 'slug' => 'istanbul']);
        $taxonomy = Taxonomy::create(['name' => 'Kombi', 'slug' => 'kombi']);
        
        ContentNode::create([
            'seo_title' => 'İstanbul Kombi',
            'slug' => 'istanbul-kombi',
            'body_content' => 'Test content',
            'taxonomy_id' => $taxonomy->id,
            'location_id' => $location->id,
        ]);

        $this->assertCount(1, $location->contentNodes);
    }
}