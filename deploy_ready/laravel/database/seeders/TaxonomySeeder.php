<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Taxonomy;

class TaxonomySeeder extends Seeder
{
    public function run(): void
    {
        $kategoriler = [
            ['name' => 'Klima Servisi', 'slug' => 'klima-servisi'],
            ['name' => 'Kombi Servisi', 'slug' => 'kombi-servisi'],
            ['name' => 'Petek Temizliği', 'slug' => 'petek-temizligi'],
            ['name' => 'Doğalgaz Tesisatı', 'slug' => 'dogalgaz-tesisati'],
            ['name' => 'Su Tesisatı', 'slug' => 'su-tesisati'],
            ['name' => 'Elektrikçi', 'slug' => 'elektrikci'],
            ['name' => 'Boyacı', 'slug' => 'boyaci'],
            ['name' => 'Tadilat', 'slug' => 'tadilat'],
            ['name' => 'Cam Balkon', 'slug' => 'cam-balkon'],
            ['name' => 'Parke Döşeme', 'slug' => 'parke-doseme'],
            ['name' => 'Evden Eve Nakliyat', 'slug' => 'evden-eve-nakliyat'],
            ['name' => 'Eşya Depolama', 'slug' => 'esya-depolama'],
            ['name' => 'Ev Temizliği', 'slug' => 'ev-temizligi'],
            ['name' => 'Ofis Temizliği', 'slug' => 'ofis-temizligi'],
            ['name' => 'Halı Yıkama', 'slug' => 'hali-yikama'],
            ['name' => 'Diş Hekimi', 'slug' => 'dis-hekimi'],
            ['name' => 'Nöbetçi Eczane', 'slug' => 'nobetci-eczane'],
            ['name' => 'Psikolog', 'slug' => 'psikolog'],
            ['name' => 'Fizyoterapi', 'slug' => 'fizyoterapi'],
            ['name' => 'Veteriner', 'slug' => 'veteriner'],
            ['name' => 'Pet Shop', 'slug' => 'pet-shop'],
            ['name' => 'Oto Ekspertiz', 'slug' => 'oto-ekspertiz'],
            ['name' => 'Oto Kaporta', 'slug' => 'oto-kaporta'],
            ['name' => 'Oto Lastik', 'slug' => 'oto-lastik'],
            ['name' => 'Çilingir', 'slug' => 'cilingir'],
            ['name' => 'Özel Ders', 'slug' => 'ozel-ders'],
            ['name' => 'Sürücü Kursu', 'slug' => 'surucu-kursu'],
            ['name' => 'Döviz Kurları', 'slug' => 'doviz-kurlari'],
            ['name' => 'Altın Fiyatları', 'slug' => 'altin-fiyatlari'],
            ['name' => 'Kripto Para', 'slug' => 'kripto-para'],
        ];

        foreach ($kategoriler as $kat) {
            Taxonomy::firstOrCreate(
                ['slug' => $kat['slug']],
                ['name' => $kat['name'], 'parent_id' => null]
            );
        }

        $this->command->info(count($kategoriler) . ' kategori yüklendi.');
    }
}
