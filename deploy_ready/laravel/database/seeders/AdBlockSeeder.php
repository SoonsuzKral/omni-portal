<?php

namespace Database\Seeders;

use App\Models\GlobalAdBlock;
use Illuminate\Database\Seeder;

class AdBlockSeeder extends Seeder
{
    public function run(): void
    {
        GlobalAdBlock::create([
            'name' => 'Genel Google Banner',
            'position' => 'header',
            'network_type' => 'Safe',
            'is_global' => true,
            'script' => '<div style="background:#ffd700; color:#000; padding:20px; text-align:center; font-weight:bold; border:2px dashed #000;">💰 REKLAM TESTI: GENEL SAFE BANNER (Her yerde çıkar)</div>',
            'active' => true,
            'cpm_note' => '$2.00 CPM Test',
        ]);

        GlobalAdBlock::create([
            'name' => 'Klima Servis Reklamı',
            'position' => 'mid',
            'network_type' => 'Safe',
            'is_global' => false,
            'taxonomy_id' => 5,
            'script' => '<div style="background:#00bcd4; color:#fff; padding:20px; text-align:center; font-weight:bold;">🌬️ KLIMA OZEL REKLAMI: SADECE KLIMA SAYFALARINDA ÇIKAR!</div>',
            'active' => true,
            'cpm_note' => '$3.50 CPM - Klima Kategorisi',
        ]);

        GlobalAdBlock::create([
            'name' => 'Adult Aggressive Pop',
            'position' => 'sticky',
            'network_type' => 'Restricted',
            'is_global' => true,
            'script' => '<div style="background:#ff0000; color:#fff; padding:10px; text-align:center; font-weight:bold; position:fixed; bottom:0; width:100%; z-index:999;">🔥 RESTRICTED POPUP: SADECE HASSAS İÇERİKTE ÇIKAR! 🔥</div>',
            'active' => true,
            'cpm_note' => '$8.00 CPM Restricted',
        ]);
    }
}