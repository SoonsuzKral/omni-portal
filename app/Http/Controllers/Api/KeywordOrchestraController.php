<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Keyword;
use App\Models\Taxonomy;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KeywordOrchestraController extends Controller
{
    public function importKeywords(Request $request)
    {
        $validated = $request->validate([
            'keywords' => 'required|array',
            'keywords.*.keyword' => 'required|string',
            'keywords.*.search_volume' => 'nullable|integer',
            'keywords.*.difficulty' => 'nullable|integer',
            'keywords.*.language' => 'nullable|string|max:10',
            'keywords.*.country_code' => 'nullable|string|max:2',
            'keywords.*.category' => 'nullable|string',
            'location' => 'nullable|string',
        ]);

        $imported = 0;
        $updated = 0;

        foreach ($validated['keywords'] as $kw) {
            $keyword = $kw['keyword'];
            $language = $kw['language'] ?? $validated['language'] ?? 'en';
            $countryCode = $kw['country_code'] ?? $validated['country_code'] ?? 'US';
            $searchVolume = $kw['search_volume'] ?? 0;
            $difficulty = $kw['difficulty'] ?? 0;
            $categoryName = $kw['category'] ?? $validated['category'] ?? null;

            $location = Location::where('slug', strtolower($countryCode))->first();
            $category = $categoryName ? Taxonomy::where('slug', str_slug($categoryName))->first() : null;

            $existing = Keyword::where('keyword', $keyword)
                ->where('language', $language)
                ->where('location_id', $location?->id)
                ->first();

            if ($existing) {
                $existing->update([
                    'search_volume' => $searchVolume,
                    'difficulty' => $difficulty,
                    'category_id' => $category?->id,
                ]);
                $updated++;
            } else {
                Keyword::create([
                    'keyword' => $keyword,
                    'slug' => str_slug($keyword),
                    'language' => $language,
                    'search_volume' => $searchVolume,
                    'difficulty' => $difficulty,
                    'category_id' => $category?->id,
                    'location_id' => $location?->id,
                    'is_auto_generated' => true,
                ]);
                $imported++;
            }
        }

        Log::info("Keyword Orchestra: Imported {$imported}, Updated {$updated} keywords");

        return response()->json([
            'success' => true,
            'imported' => $imported,
            'updated' => $updated,
            'total' => $imported + $updated,
        ]);
    }

    public function syncCountries()
    {
        $countries = [
            ['code' => 'TR', 'name' => 'Turkey', 'slug' => 'turkey'],
            ['code' => 'US', 'name' => 'United States', 'slug' => 'united-states'],
            ['code' => 'GB', 'name' => 'United Kingdom', 'slug' => 'united-kingdom'],
            ['code' => 'DE', 'name' => 'Germany', 'slug' => 'germany'],
            ['code' => 'FR', 'name' => 'France', 'slug' => 'france'],
            ['code' => 'ES', 'name' => 'Spain', 'slug' => 'spain'],
            ['code' => 'IT', 'name' => 'Italy', 'slug' => 'italy'],
            ['code' => 'NL', 'name' => 'Netherlands', 'slug' => 'netherlands'],
            ['code' => 'BE', 'name' => 'Belgium', 'slug' => 'belgium'],
            ['code' => 'AT', 'name' => 'Austria', 'slug' => 'austria'],
            ['code' => 'CH', 'name' => 'Switzerland', 'slug' => 'switzerland'],
            ['code' => 'PL', 'name' => 'Poland', 'slug' => 'poland'],
            ['code' => 'SE', 'name' => 'Sweden', 'slug' => 'sweden'],
            ['code' => 'NO', 'name' => 'Norway', 'slug' => 'norway'],
            ['code' => 'DK', 'name' => 'Denmark', 'slug' => 'denmark'],
            ['code' => 'FI', 'name' => 'Finland', 'slug' => 'finland'],
            ['code' => 'PT', 'name' => 'Portugal', 'slug' => 'portugal'],
            ['code' => 'GR', 'name' => 'Greece', 'slug' => 'greece'],
            ['code' => 'IE', 'name' => 'Ireland', 'slug' => 'ireland'],
            ['code' => 'CZ', 'name' => 'Czech Republic', 'slug' => 'czech-republic'],
            ['code' => 'HU', 'name' => 'Hungary', 'slug' => 'hungary'],
            ['code' => 'RO', 'name' => 'Romania', 'slug' => 'romania'],
            ['code' => 'BG', 'name' => 'Bulgaria', 'slug' => 'bulgaria'],
            ['code' => 'SK', 'name' => 'Slovakia', 'slug' => 'slovakia'],
            ['code' => 'HR', 'name' => 'Croatia', 'slug' => 'croatia'],
            ['code' => 'SI', 'name' => 'Slovenia', 'slug' => 'slovenia'],
            ['code' => 'RS', 'name' => 'Serbia', 'slug' => 'serbia'],
            ['code' => 'UA', 'name' => 'Ukraine', 'slug' => 'ukraine'],
            ['code' => 'RU', 'name' => 'Russia', 'slug' => 'russia'],
            ['code' => 'BR', 'name' => 'Brazil', 'slug' => 'brazil'],
            ['code' => 'AR', 'name' => 'Argentina', 'slug' => 'argentina'],
            ['code' => 'MX', 'name' => 'Mexico', 'slug' => 'mexico'],
            ['code' => 'CO', 'name' => 'Colombia', 'slug' => 'colombia'],
            ['code' => 'CL', 'name' => 'Chile', 'slug' => 'chile'],
            ['code' => 'PE', 'name' => 'Peru', 'slug' => 'peru'],
            ['code' => 'AU', 'name' => 'Australia', 'slug' => 'australia'],
            ['code' => 'NZ', 'name' => 'New Zealand', 'slug' => 'new-zealand'],
            ['code' => 'JP', 'name' => 'Japan', 'slug' => 'japan'],
            ['code' => 'KR', 'name' => 'South Korea', 'slug' => 'south-korea'],
            ['code' => 'IN', 'name' => 'India', 'slug' => 'india'],
            ['code' => 'ID', 'name' => 'Indonesia', 'slug' => 'indonesia'],
            ['code' => 'TH', 'name' => 'Thailand', 'slug' => 'thailand'],
            ['code' => 'VN', 'name' => 'Vietnam', 'slug' => 'vietnam'],
            ['code' => 'MY', 'name' => 'Malaysia', 'slug' => 'malaysia'],
            ['code' => 'SG', 'name' => 'Singapore', 'slug' => 'singapore'],
            ['code' => 'PH', 'name' => 'Philippines', 'slug' => 'philippines'],
            ['code' => 'PK', 'name' => 'Pakistan', 'slug' => 'pakistan'],
            ['code' => 'EG', 'name' => 'Egypt', 'slug' => 'egypt'],
            ['code' => 'SA', 'name' => 'Saudi Arabia', 'slug' => 'saudi-arabia'],
            ['code' => 'AE', 'name' => 'United Arab Emirates', 'slug' => 'uae'],
            ['code' => 'IL', 'name' => 'Israel', 'slug' => 'israel'],
            ['code' => 'ZA', 'name' => 'South Africa', 'slug' => 'south-africa'],
            ['code' => 'NG', 'name' => 'Nigeria', 'slug' => 'nigeria'],
            ['code' => 'KE', 'name' => 'Kenya', 'slug' => 'kenya'],
            ['code' => 'CA', 'name' => 'Canada', 'slug' => 'canada'],
        ];

        $created = 0;
        foreach ($countries as $country) {
            if (!Location::where('slug', $country['slug'])->exists()) {
                Location::create([
                    'name' => $country['name'],
                    'slug' => $country['slug'],
                ]);
                $created++;
            }
        }

        return response()->json([
            'success' => true,
            'total_countries' => count($countries),
            'created' => $created,
        ]);
    }

    public function getStatus()
    {
        $totalKeywords = Keyword::count();
        $byLanguage = Keyword::selectRaw('language, COUNT(*) as count')
            ->groupBy('language')
            ->pluck('count', 'language');
        
        $byCountry = Location::withCount('keywords')
            ->having('keywords_count', '>', 0)
            ->pluck('keywords_count', 'name');

        return response()->json([
            'total_keywords' => $totalKeywords,
            'by_language' => $byLanguage,
            'by_country' => $byCountry,
            'countries_available' => Location::count(),
        ]);
    }
}