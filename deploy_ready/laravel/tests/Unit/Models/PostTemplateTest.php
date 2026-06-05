<?php

namespace Tests\Unit\Models;

use App\Models\PostTemplate;
use App\Models\Taxonomy;
use App\Models\ContentNode;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_post_template(): void
    {
        $template = PostTemplate::create([
            'name' => 'Servis Şablonu',
            'slug' => 'servis-sablonu',
            'template_body' => '<h1>{{title}}</h1><p>{{content}}</p>',
        ]);

        $this->assertDatabaseHas('post_templates', [
            'name' => 'Servis Şablonu',
            'slug' => 'servis-sablonu',
        ]);
    }

    public function test_can_belong_to_taxonomy(): void
    {
        $taxonomy = Taxonomy::create(['name' => 'Kombi', 'slug' => 'kombi']);
        
        $template = PostTemplate::create([
            'name' => 'Kombi Şablonu',
            'slug' => 'kombi-sablonu',
            'template_body' => '<h1>{{title}}</h1>',
            'taxonomy_id' => $taxonomy->id,
        ]);

        $this->assertEquals($taxonomy->id, $template->taxonomy->id);
    }

    public function test_template_can_have_content_nodes(): void
    {
        $template = PostTemplate::create([
            'name' => 'Test',
            'slug' => 'test',
            'template_body' => '<h1>{{title}}</h1>',
        ]);

        $taxonomy = Taxonomy::create(['name' => 'Kombi', 'slug' => 'kombi']);
        $location = Location::create(['name' => 'İstanbul', 'slug' => 'istanbul']);

        ContentNode::create([
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'seo_title' => 'Test',
            'slug' => 'test',
            'body_content' => 'Test',
            'taxonomy_id' => $taxonomy->id,
            'location_id' => $location->id,
            'post_template_id' => $template->id,
        ]);

        $this->assertCount(1, $template->contentNodes);
    }
}