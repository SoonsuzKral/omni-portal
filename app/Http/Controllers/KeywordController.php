<?php

namespace App\Http\Controllers;

use App\Models\Keyword;
use App\Models\Taxonomy;
use App\Models\Location;
use App\Models\ContentNode;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class KeywordController extends Controller
{
    /**
     * Display trending keywords.
     */
    public function trending(Request $request)
    {
        $language = $request->get('lang', 'tr');

        $keywords = Keyword::byLanguage($language)
            ->where('is_trending', true)
            ->with(['category', 'location'])
            ->orderBy('search_volume', 'desc')
            ->paginate(50);

        // Fallback: if no trending keywords, show most recent auto-generated
        if ($keywords->isEmpty()) {
            $keywords = Keyword::byLanguage($language)
                ->with(['category', 'location'])
                ->orderBy('search_volume', 'desc')
                ->paginate(50);
        }

        // Daily Turkey trends — top Turkish keywords by search volume
        $turkeyTrends = Keyword::where('language', 'tr')
            ->with(['category', 'location'])
            ->orderBy('search_volume', 'desc')
            ->limit(20)
            ->get();

        return view('keyword.trending', compact('keywords', 'language', 'turkeyTrends'));
    }

    /**
     * Display keyword suggestions based on search.
     */
    public function suggest(Request $request)
    {
        $query = $request->get('q', '');
        $language = $request->get('lang', 'tr');

        $keywords = Keyword::byLanguage($language)
            ->where('keyword', 'like', "%{$query}%")
            ->orderBy('search_volume', 'desc')
            ->limit(20)
            ->get();

        return response()->json($keywords);
    }

    /**
     * Generate auto-keywords based on existing content.
     */
    public function generate(Request $request)
    {
        $language = $request->get('lang', 'tr');
        $targetCount = (int) $request->get('count', 200);

        // Pull ALL taxonomies and locations (no content pre-requisite)
        $allTaxonomies = Taxonomy::inRandomOrder()->limit(50)->get();
        $allLocations = Location::whereNull('parent_id')->inRandomOrder()->limit(50)->get();

        if ($allTaxonomies->isEmpty()) {
            $allTaxonomies = Taxonomy::inRandomOrder()->limit(50)->get();
        }
        if ($allLocations->isEmpty()) {
            $allLocations = Location::whereNull('parent_id')->inRandomOrder()->limit(50)->get();
        }

        $generated = 0;
        $maxAttempts = $targetCount * 3;

        $trPatterns = [
            '{$loc} {$cat}',
            '{$cat} {$loc}',
            'en iyi {$cat} {$loc}',
            '{$loc} {$cat} fiyat',
            '{$cat} {$loc} ücreti',
            '{$loc} {$cat} hizmeti',
            '{$loc} {$cat} şirketi',
            'ucuz {$cat} {$loc}',
            'profesyonel {$cat} {$loc}',
            '{$loc} en iyi {$cat}',
            '{$cat} {$loc} telefon',
            '{$cat} {$loc} randevu',
            '{$loc} {$cat} kampanya',
        ];

        $langPatterns = [
            'en' => ['best {$cat} in {$loc}', 'top {$cat} {$loc}', '{$cat} {$loc} cheap', '{$loc} {$cat} services', 'professional {$cat} {$loc}'],
            'ar' => ['أفضل {$cat} في {$loc}', '{$cat} {$loc} رخيص', '{$cat} {$loc} محترف'],
            'ru' => ['лучший {$cat} в {$loc}', '{$cat} {$loc} дешево', 'профессиональный {$cat} {$loc}'],
            'fa' => ['بهترین {$cat} در {$loc}', '{$cat} {$loc} ارزان', 'حرفه‌ای {$cat} {$loc}'],
            'fr' => ['meilleur {$cat} à {$loc}', '{$cat} {$loc} pas cher', '{$cat} {$loc} professionnel'],
        ];

        $allLanguages = ['tr', 'en', 'ar', 'ru', 'fa', 'fr'];

        for ($attempt = 0; $attempt < $maxAttempts && $generated < $targetCount; $attempt++) {
            $taxonomy = $allTaxonomies->random();
            $location = $allLocations->random();
            $lang = $allLanguages[array_rand($allLanguages)];

            if ($lang === 'tr') {
                $pattern = $trPatterns[array_rand($trPatterns)];
            } else {
                $patterns = $langPatterns[$lang] ?? $langPatterns['en'];
                $pattern = $patterns[array_rand($patterns)];
            }

            $keyword = str_replace(['{$loc}', '{$cat}'], [$location->name, $taxonomy->name], $pattern);

            if (Keyword::where('keyword', $keyword)->where('language', $lang)->exists()) {
                continue;
            }

            Keyword::create([
                'keyword' => $keyword,
                'slug' => Str::slug($keyword),
                'language' => $lang,
                'category_id' => $taxonomy->id,
                'location_id' => $location->id,
                'search_volume' => rand(100, 10000),
                'difficulty' => rand(20, 80),
                'is_auto_generated' => true,
            ]);
            $generated++;
        }

        Log::info("KeywordGenerator: generated {$generated} new keywords (lang={$language})");

        return response()->json([
            'success' => true,
            'generated' => $generated,
            'message' => "{$generated} keywords generated successfully"
        ]);
    }

    /**
     * Add keywords for a specific language.
     */
    protected function addLanguageKeywords(string $language, $taxonomies, $locations)
    {
        $prefixes = [
            'en' => ['best', 'top', 'cheap', 'professional'],
            'ar' => ['افضل', 'ارخص', 'احترافي'],
            'ru' => ['лучший', 'дешевый', 'профессиональный'],
            'fa' => ['بهترین', 'ارزان', 'حرفه ای'],
            'fr' => ['meilleur', 'pas cher', 'professionnel'],
        ];

        $prefix = $prefixes[$language] ?? [];

        foreach ($taxonomies->take(10) as $taxonomy) {
            foreach ($locations->take(5) as $location) {
                foreach ($prefix as $p) {
                    $keyword = "{$p} {$taxonomy->name} in {$location->name}";

                    if (!Keyword::where('keyword', $keyword)->where('language', $language)->exists()) {
                        Keyword::create([
                            'keyword' => $keyword,
                            'slug' => Str::slug($keyword),
                            'language' => $language,
                            'category_id' => $taxonomy->id,
                            'location_id' => $location->id,
                            'search_volume' => rand(50, 5000),
                            'difficulty' => rand(30, 90),
                            'is_auto_generated' => true,
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Auto-create content from keywords.
     */
    public function autoCreateContent(Request $request)
    {
        $limit = $request->get('limit', 50);

        // Get keywords without content (trending first, then any auto-generated)
        $keywords = Keyword::whereDoesntHave('contentNodes')
            ->orderBy('is_trending', 'desc')
            ->orderBy('search_volume', 'desc')
            ->limit($limit)
            ->get();

        $created = 0;

        foreach ($keywords as $keyword) {
            // Create content node for this keyword
            $locationName = $keyword->location?->name ?: 'Türkiye';
            $categoryName = $keyword->category?->name ?: 'sektör';
            $template = "En iyi {$keyword->keyword} hizmeti. Profesyonel ekibimizle {$locationName} genelinde hizmet vermekteyiz. ";
            $template .= "Uygun fiyatlar ve kalite garantisi ile {$categoryName} alanında uzman kadromuz hizmetinizde. ";
            $template .= "Bizi tercih ettiğiniz için teşekkür ederiz. Hemen arayın!";

            ContentNode::create([
                'uuid' => Str::uuid()->toString(),
                'seo_title' => ucfirst($keyword->keyword),
                'slug' => $keyword->slug,
                'body_content' => $template,
                'taxonomy_id' => $keyword->category_id,
                'location_id' => $keyword->location_id,
                'publish_date' => now(),
                'is_restricted_content' => false,
            ]);

            $created++;
        }

        return response()->json([
            'success' => true,
            'created' => $created,
            'message' => "{$created} content pages created from keywords"
        ]);
    }
}