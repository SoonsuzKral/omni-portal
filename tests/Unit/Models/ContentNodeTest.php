<?php

namespace Tests\Unit\Models;

use App\Models\ContentNode;
use App\Models\Taxonomy;
use App\Models\Location;
use App\Models\PostTemplate;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class ContentNodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_content_node(): void
    {
        $taxonomy = Taxonomy::create(['name' => 'Kombi', 'slug' => 'kombi']);
        $location = Location::create(['name' => 'İstanbul', 'slug' => 'istanbul']);

        $contentNode = ContentNode::create([
            'uuid' => Str::uuid()->toString(),
            'seo_title' => 'İstanbul Kombi Servisi',
            'slug' => 'istanbul-kombi-servisi',
            'body_content' => '<p>En iyi kombi servisi</p>',
            'taxonomy_id' => $taxonomy->id,
            'location_id' => $location->id,
        ]);

        $this->assertDatabaseHas('content_nodes', [
            'seo_title' => 'İstanbul Kombi Servisi',
            'slug' => 'istanbul-kombi-servisi',
        ]);
    }

    public function test_title_attribute_returns_seo_title(): void
    {
        $taxonomy = Taxonomy::create(['name' => 'Kombi', 'slug' => 'kombi']);
        $location = Location::create(['name' => 'İstanbul', 'slug' => 'istanbul']);

        $contentNode = ContentNode::create([
            'uuid' => Str::uuid()->toString(),
            'seo_title' => 'İstanbul Kombi',
            'slug' => 'istanbul-kombi',
            'body_content' => 'Test',
            'taxonomy_id' => $taxonomy->id,
            'location_id' => $location->id,
        ]);

        $this->assertEquals('İstanbul Kombi', $contentNode->title);
    }

    public function test_restricted_scope_filters_restricted_content(): void
    {
        $taxonomy = Taxonomy::create(['name' => 'Kombi', 'slug' => 'kombi']);
        $location = Location::create(['name' => 'İstanbul', 'slug' => 'istanbul']);

        ContentNode::create([
            'uuid' => Str::uuid()->toString(),
            'seo_title' => 'Restricted Content',
            'slug' => 'restricted',
            'body_content' => 'Test',
            'taxonomy_id' => $taxonomy->id,
            'location_id' => $location->id,
            'is_restricted_content' => true,
        ]);

        ContentNode::create([
            'uuid' => Str::uuid()->toString(),
            'seo_title' => 'Normal Content',
            'slug' => 'normal',
            'body_content' => 'Test',
            'taxonomy_id' => $taxonomy->id,
            'location_id' => $location->id,
            'is_restricted_content' => false,
        ]);

        $this->assertCount(1, ContentNode::restricted()->get());
    }

    public function test_non_restricted_scope_filters_normal_content(): void
    {
        $taxonomy = Taxonomy::create(['name' => 'Kombi', 'slug' => 'kombi']);
        $location = Location::create(['name' => 'İstanbul', 'slug' => 'istanbul']);

        ContentNode::create([
            'uuid' => Str::uuid()->toString(),
            'seo_title' => 'Restricted',
            'slug' => 'restricted',
            'body_content' => 'Test',
            'taxonomy_id' => $taxonomy->id,
            'location_id' => $location->id,
            'is_restricted_content' => true,
        ]);

        ContentNode::create([
            'uuid' => Str::uuid()->toString(),
            'seo_title' => 'Normal',
            'slug' => 'normal',
            'body_content' => 'Test',
            'taxonomy_id' => $taxonomy->id,
            'location_id' => $location->id,
            'is_restricted_content' => false,
        ]);

        $this->assertCount(1, ContentNode::nonRestricted()->get());
    }

    public function test_can_belong_to_post_template(): void
    {
        $taxonomy = Taxonomy::create(['name' => 'Kombi', 'slug' => 'kombi']);
        $location = Location::create(['name' => 'İstanbul', 'slug' => 'istanbul']);
        $template = PostTemplate::create(['name' => 'Servis Şablonu', 'slug' => 'servis-sablonu', 'template_body' => '<h1>{{title}}</h1>']);

        $contentNode = ContentNode::create([
            'uuid' => Str::uuid()->toString(),
            'seo_title' => 'Test',
            'slug' => 'test',
            'body_content' => 'Test',
            'taxonomy_id' => $taxonomy->id,
            'location_id' => $location->id,
            'post_template_id' => $template->id,
        ]);

        $this->assertEquals($template->id, $contentNode->postTemplate->id);
    }
}