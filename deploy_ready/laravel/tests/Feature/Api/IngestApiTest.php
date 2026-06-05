<?php

namespace Tests\Feature\Api;

use App\Models\Taxonomy;
use App\Models\Location;
use App\Models\LiveDataVault;
use App\Models\PostTemplate;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;

class IngestApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_health_endpoint_returns_ok(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200)
            ->assertJson(['status' => 'ok'])
            ->assertJsonStructure(['status', 'timestamp', 'version', 'queue_driver']);
    }

    public function test_status_endpoint_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/ingest/status');

        $response->assertStatus(401);
    }

    public function test_status_endpoint_returns_counts_with_auth(): void
    {
        Sanctum::actingAs($this->user);

        Taxonomy::create(['name' => 'Kombi', 'slug' => 'kombi']);
        Location::create(['name' => 'İstanbul', 'slug' => 'istanbul']);

        $response = $this->getJson('/api/v1/ingest/status');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'totals', 'queue']);
    }

    public function test_ingest_endpoint_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/ingest', []);

        $response->assertStatus(401);
    }

    public function test_ingest_creates_taxonomy(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/ingest', [
            'taxonomies' => [
                ['name' => 'Kombi', 'slug' => 'kombi'],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('taxonomies', ['slug' => 'kombi']);
    }

    public function test_ingest_creates_location(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/ingest', [
            'locations' => [
                ['name' => 'İstanbul', 'slug' => 'istanbul'],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('locations', ['slug' => 'istanbul']);
    }

    public function test_ingest_creates_content_node(): void
    {
        Sanctum::actingAs($this->user);

        Taxonomy::create(['name' => 'Kombi', 'slug' => 'kombi']);
        Location::create(['name' => 'İstanbul', 'slug' => 'istanbul']);

        $response = $this->postJson('/api/v1/ingest', [
            'content_nodes' => [
                [
                    'title' => 'İstanbul Kombi Servisi',
                    'body_content' => '<p>En iyi kombi servisi</p>',
                    'taxonomy_slug' => 'kombi',
                    'location_slug' => 'istanbul',
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('content_nodes', [
            'seo_title' => 'İstanbul Kombi Servisi',
        ]);
    }

    public function test_ingest_creates_live_data(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/ingest', [
            'live_data' => [
                ['key' => 'usd_try', 'value' => '32.50', 'display_name' => 'Dolar TL'],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('live_data_vaults', ['key' => 'usd_try', 'value' => '32.50']);
    }

    public function test_ingest_validates_taxonomy_slug_format(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/ingest', [
            'taxonomies' => [
                ['name' => 'Test', 'slug' => 'invalid slug!'],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_ingest_validates_live_data_key_format(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/ingest', [
            'live_data' => [
                ['key' => 'invalid key!', 'value' => 'test'],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_ingest_creates_hierarchical_taxonomy(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/ingest', [
            'taxonomies' => [
                ['name' => 'Isıtma', 'slug' => 'isitma'],
                ['name' => 'Kombi', 'slug' => 'kombi', 'parent_slug' => 'isitma'],
            ],
        ]);

        $response->assertStatus(200);

        $kombi = Taxonomy::where('slug', 'kombi')->first();
        $this->assertNotNull($kombi->parent_id);
    }
}