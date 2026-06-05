<?php

namespace Tests\Unit\Models;

use App\Models\LiveDataVault;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LiveDataVaultTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_live_data(): void
    {
        $liveData = LiveDataVault::create([
            'key' => 'usd_try',
            'value' => '32.50',
            'display_name' => 'Dolar TL',
        ]);

        $this->assertDatabaseHas('live_data_vaults', [
            'key' => 'usd_try',
            'value' => '32.50',
        ]);
    }

    public function test_key_must_be_unique(): void
    {
        LiveDataVault::create([
            'key' => 'usd_try',
            'value' => '32.50',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        
        LiveDataVault::create([
            'key' => 'usd_try',
            'value' => '33.00',
        ]);
    }

    public function test_can_update_live_data_by_key(): void
    {
        LiveDataVault::create(['key' => 'usd_try', 'value' => '32.50']);
        
        $updated = LiveDataVault::updateOrCreate(
            ['key' => 'usd_try'],
            ['value' => '33.00']
        );

        $this->assertEquals('33.00', $updated->value);
    }
}