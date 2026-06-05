<?php

namespace Database\Seeders;

use App\Models\Taxonomy;
use App\Models\Location;
use App\Models\ContentNode;
use App\Models\Keyword;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create Taxonomies (Categories)
        $taxonomies = [
            ['name' => 'Kombi', 'slug' => 'kombi'],
            ['name' => 'Klima', 'slug' => 'klima'],
            ['name' => 'Petek', 'slug' => 'petek'],
            ['name' => 'Tesisat', 'slug' => 'tesisat'],
            ['name' => 'Doğalgaz', 'slug' => 'dogalgaz'],
            ['name' => 'Boru', 'slug' => 'boru'],
            ['name' => 'Musluk', 'slug' => 'musluk'],
            ['name' => 'Tuvalet', 'slug' => 'tuvalet'],
        ];

        foreach ($taxonomies as $tax) {
            Taxonomy::firstOrCreate(['slug' => $tax['slug']], $tax);
        }

        // Create Locations (Cities)
        $locations = [
            ['name' => 'İstanbul', 'slug' => 'istanbul'],
            ['name' => 'Ankara', 'slug' => 'ankara'],
            ['name' => 'İzmir', 'slug' => 'izmir'],
            ['name' => 'Bursa', 'slug' => 'bursa'],
            ['name' => 'Antalya', 'slug' => 'antalya'],
            ['name' => 'Adana', 'slug' => 'adana'],
            ['name' => 'Konya', 'slug' => 'konya'],
            ['name' => 'Gaziantep', 'slug' => 'gaziantep'],
        ];

        foreach ($locations as $loc) {
            Location::firstOrCreate(['slug' => $loc['slug']], $loc);
        }

        // Create some Content Nodes
        $kombi = Taxonomy::where('slug', 'kombi')->first();
        $istanbul = Location::where('slug', 'istanbul')->first();
        $ankara = Location::where('slug', 'ankara')->first();

        if ($kombi && $istanbul) {
            ContentNode::firstOrCreate(
                ['slug' => 'istanbul-kombi-servis'],
                [
                    'uuid' => Str::uuid()->toString(),
                    'seo_title' => 'İstanbul Kombi Servisi - En İyi Kombi Tamiri',
                    'body_content' => 'İstanbul genelinde kombi servisi hizmeti. Arıza, bakım ve montaj işlemleri için profesyonel ekibimizle hizmetinizdeyiz. 7/24 teknik destek.',
                    'taxonomy_id' => $kombi->id,
                    'location_id' => $istanbul->id,
                    'publish_date' => now(),
                    'page_views' => rand(100, 5000),
                ]
            );
        }

        if ($kombi && $ankara) {
            ContentNode::firstOrCreate(
                ['slug' => 'ankara-kombi-servis'],
                [
                    'uuid' => Str::uuid()->toString(),
                    'seo_title' => 'Ankara Kombi Servisi - Hızlı ve Güvenilir',
                    'body_content' => 'Ankara kombi servis hizmetleri. Tüm marka kombilerin bakım ve onarımı. Uygun fiyatlar ve garantili işçilik.',
                    'taxonomy_id' => $kombi->id,
                    'location_id' => $ankara->id,
                    'publish_date' => now(),
                    'page_views' => rand(50, 3000),
                ]
            );
        }

        // Create Keywords (Multi-language)
        $languages = ['tr', 'en', 'ar', 'ru', 'fa', 'fr'];
        $trendingTerms = ['kombi servisi', 'klima bakım', 'tesisatçı', 'doğalgaz', 'petek temizliği'];

        foreach ($languages as $lang) {
            foreach ($trendingTerms as $term) {
                $keyword = "{$term} in " . ($lang === 'tr' ? 'Turkey' : match($lang) {
                    'en' => 'Turkey',
                    'ar' => 'تركيا',
                    'ru' => 'Турция',
                    'fa' => 'ترکیه',
                    'fr' => 'Turquie',
                });

                Keyword::firstOrCreate(
                    ['keyword' => $keyword, 'language' => $lang],
                    [
                        'slug' => Str::slug($keyword) . '-' . $lang,
                        'language' => $lang,
                        'category_id' => $kombi?->id,
                        'location_id' => $istanbul?->id,
                        'search_volume' => rand(100, 10000),
                        'difficulty' => rand(20, 80),
                        'is_trending' => rand(0, 1) === 1,
                        'clicks' => rand(10, 1000),
                    ]
                );
            }
        }

        echo "✅ Demo data seeded successfully!\n";
    }
}