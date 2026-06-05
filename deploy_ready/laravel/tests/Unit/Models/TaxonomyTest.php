<?php

namespace Tests\Unit\Models;

use App\Models\Taxonomy;
use App\Models\Location;
use App\Models\ContentNode;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaxonomyTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_taxonomy(): void
    {
        $taxonomy = Taxonomy::create([
            'name' => 'Kombi',
            'slug' => 'kombi',
        ]);

        $this->assertDatabaseHas('taxonomies', [
            'name' => 'Kombi',
            'slug' => 'kombi',
        ]);
    }

    public function test_can_create_child_taxonomy(): void
    {
        $parent = Taxonomy::create(['name' => 'Isıtma', 'slug' => 'isitma']);
        $child = Taxonomy::create(['name' => 'Kombi', 'slug' => 'kombi', 'parent_id' => $parent->id]);

        $this->assertEquals($parent->id, $child->parent->id);
    }

    public function test_can_get_children(): void
    {
        $parent = Taxonomy::create(['name' => 'Isıtma', 'slug' => 'isitma']);
        Taxonomy::create(['name' => 'Kombi', 'slug' => 'kombi', 'parent_id' => $parent->id]);
        Taxonomy::create(['name' => 'Kalorifer', 'slug' => 'kalorifer', 'parent_id' => $parent->id]);

        $this->assertCount(2, $parent->children);
    }

    public function test_can_get_content_nodes(): void
    {
        $taxonomy = Taxonomy::create(['name' => 'Kombi', 'slug' => 'kombi']);
        $location = Location::create(['name' => 'İstanbul', 'slug' => 'istanbul']);
        
        ContentNode::create([
            'seo_title' => 'İstanbul Kombi',
            'slug' => 'istanbul-kombi',
            'body_content' => 'Test content',
            'taxonomy_id' => $taxonomy->id,
            'location_id' => $location->id,
        ]);

        $this->assertCount(1, $taxonomy->contentNodes);
    }
}