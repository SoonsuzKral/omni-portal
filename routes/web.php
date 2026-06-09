<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\TaxonomyController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\KeywordController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\AdBlockDebugController;
use App\Http\Controllers\ServiceManagerController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

// Dynamic robots.txt
Route::get('/robots.txt', function () {
    $sitemapUrl = config('app.url') . '/sitemap.xml';
    $content = "User-agent: *\n";
    $content .= "Allow: /\n\n";
    $content .= "Sitemap: {$sitemapUrl}\n\n";

    try {
        $indexedCities = \App\Http\Controllers\ContentController::INDEXED_CITIES;
        $indexedCategories = \App\Http\Controllers\ContentController::INDEXED_CATEGORIES;
        $totalContent = \App\Models\ContentNode::whereNotNull('publish_date')
            ->whereHas('location', fn($q) => $q->whereIn('slug', $indexedCities))
            ->whereHas('taxonomy', fn($q) => $q->whereIn('slug', $indexedCategories))
            ->count();
        $totalShards = max(1, (int) ceil($totalContent / 45000));
        for ($i = 1; $i <= $totalShards; $i++) {
            $content .= "Sitemap: " . url("/sitemap-content-{$i}.xml") . "\n";
        }
    } catch (\Exception $e) {
        $content .= "Sitemap: " . url("/sitemap-content-1.xml") . "\n";
    }

    $content .= "Sitemap: " . url("/sitemap-categories.xml") . "\n";
    $content .= "Sitemap: " . url("/sitemap-locations.xml") . "\n\n";
    $content .= "Disallow: /admin/\n";
    $content .= "Disallow: /api/\n";
    $content .= "Disallow: /_ignition/\n";
    $content .= "Disallow: /nova/\n";
    $content .= "Disallow: /horizon/\n";
    $content .= "Crawl-delay: 1\n";

    return response($content, 200, ['Content-Type' => 'text/plain']);
});

// ads.txt - Google AdSense verification (outside middleware, public)
Route::get('/ads.txt', function () {
    $adsTxt = \App\Models\LiveDataVault::where('key', 'ads_txt')->first();
    if ($adsTxt?->value) {
        $content = $adsTxt->value;
    } else {
        $physicalFile = public_path('ads.txt');
        if (file_exists($physicalFile)) {
            $content = file_get_contents($physicalFile);
        } else {
            $adClient = config('services.adsense.ad_client');
            if ($adClient) {
                $pubId = str_replace('ca-', '', $adClient);
                $content = "google.com, {$pubId}, DIRECT, f08c47fec0942fa0" . PHP_EOL;
            } else {
                $content = '# ads.txt - ' . config('app.name') . PHP_EOL . '# Configure your AdSense publisher ID to enable.' . PHP_EOL;
            }
        }
    }
    return response($content, 200, ['Content-Type' => 'text/plain']);
});

// sellers.json - IAB Ad Seller Compliance
Route::get('/sellers.json', function () {
    $publisherId = config('services.adsense.ad_client', '');
    $content = json_encode([
        'contact_email' => config('app.admin_email', 'admin@example.com'),
        'contact_address' => config('app.address', ''),
        'version' => '1.0',
        'sellers' => [
            [
                'seller_id' => $publisherId,
                'seller_type' => 'PUBLISHER',
                'name' => config('app.name', 'Omni Portal'),
                'domain' => request()->getHost(),
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    return response($content, 200, ['Content-Type' => 'application/json']);
});

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ADMIN Service Routes — PROTECTED by auth middleware
Route::middleware(['auth'])->prefix('admin/services')->group(function () {
    Route::post('/install/{service}', [ServiceManagerController::class, 'install']);
    Route::post('/enable/{service}', [ServiceManagerController::class, 'enable']);
    Route::post('/disable/{service}', [ServiceManagerController::class, 'disable']);
    Route::post('/save-service', [ServiceManagerController::class, 'saveService']);
});

// ADMIN Env-Variables Page — PROTECTED
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/env-variables/services', [ServiceManagerController::class, 'showServicesPage']);
    Route::post('/admin/env-variables/services', [ServiceManagerController::class, 'handlePagePost']);
});

// Login route — redirects to Filament admin login for auth middleware
Route::get('/login', function () {
    return redirect('/admin/login');
})->name('login');

Route::get('/logout', function () {
    Auth::logout();
    return redirect('/admin/login');
})->name('logout');

// RSS Feed for content syndication
Route::get('/rss.xml', function () {
    $content = \App\Models\ContentNode::whereNotNull('publish_date')
        ->with(['taxonomy', 'location'])
        ->orderBy('publish_date', 'desc')
        ->limit(50)
        ->get();

    $xml = '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/">';
    $xml .= '<channel>';
    $xml .= '<title>' . e(config('app.name')) . '</title>';
    $xml .= '<link>' . url('/') . '</link>';
    $xml .= '<description>Latest content from ' . e(config('app.name')) . '</description>';
    $xml .= '<language>tr</language>';
    $xml .= '<lastBuildDate>' . now()->toRssString() . '</lastBuildDate>';
    $xml .= '<atom:link href="' . url('/rss.xml') . '" rel="self" type="application/rss+xml"/>';

    foreach ($content as $node) {
        $taxSlug = $node->taxonomy?->slug ?? '';
        $locSlug = $node->location?->slug ?? '';
        $url = url("/{$taxSlug}/{$locSlug}/{$node->slug}");
        $desc = $node->meta_description ?? substr(strip_tags($node->body_content ?? ''), 0, 200);

        $xml .= '<item>';
        $xml .= '<title>' . e($node->title) . '</title>';
        $xml .= '<link>' . $url . '</link>';
        $xml .= '<guid isPermaLink="true">' . $url . '</guid>';
        $xml .= '<description>' . e($desc) . '</description>';
        $xml .= '<pubDate>' . ($node->publish_date ? $node->publish_date->toRssString() : now()->toRssString()) . '</pubDate>';
        if ($node->taxonomy) {
            $xml .= '<category>' . e($node->taxonomy->name) . '</category>';
        }
        $xml .= '</item>';
    }

    $xml .= '</channel></rss>';
    return response($xml, 200, ['Content-Type' => 'application/rss+xml']);
})->name('rss');

Route::middleware(['sql.protect'])->group(function () {

// Locale switcher (inside sql.protect group for consistent middleware)
Route::get('/lang/{locale}', function ($locale) {
    $locale = strtolower($locale);
    if (in_array($locale, ['tr', 'en', 'ru', 'ar'])) {
        session(['locale' => $locale]);
        session()->save();
        Cookie::queue('app_locale', $locale, 43200);
    }
    $previous = url()->previous();
    $current = url()->current();
    if ($previous && $previous !== $current) {
        return redirect($previous);
    }
    return redirect('/');
})->name('locale.switch');

// Home page
Route::get('/', [HomeController::class, 'index'])->name('home');

// AdBlock Debug
Route::post('/adblock/debug', [AdBlockDebugController::class, 'debug']);

// Search (slug-based using ContentController)
Route::get('/search', [ContentController::class, 'search'])->name('search');
Route::get('/search/suggest', [SearchController::class, 'api'])->name('search.suggest');

// Keywords
Route::get('/keywords/trending', [KeywordController::class, 'trending'])->name('keywords.trending');
Route::get('/keywords/suggest', [KeywordController::class, 'suggest'])->name('keywords.suggest');
Route::get('/keywords/generate', [KeywordController::class, 'generate'])->name('keywords.generate');
Route::get('/keywords/auto-create', [KeywordController::class, 'autoCreateContent'])->name('keywords.auto-create');

// Static Pages (Ad Policy Compliant)
Route::view('/privacy-policy', 'static.privacy-policy')->name('privacy-policy');
Route::view('/terms', 'static.terms')->name('terms');
Route::view('/about', 'static.about')->name('about');
Route::view('/contact', 'static.contact')->name('contact');

// Sitemap (XML) - Sharded with 45k max per shard
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/sitemap-content-{page}.xml', [SitemapController::class, 'contentShard'])->where('page', '[0-9]+');
Route::get('/sitemap-categories.xml', [SitemapController::class, 'categories']);
Route::get('/sitemap-locations.xml', [SitemapController::class, 'locations']);

// Taxonomy Routes
Route::get('/categories', [TaxonomyController::class, 'index'])->name('taxonomy.index');
Route::get('/categories/tree', [TaxonomyController::class, 'tree'])->name('taxonomy.tree');
Route::get('/categories/api', [TaxonomyController::class, 'apiTree'])->name('taxonomy.api');
Route::get('/categories/search', [TaxonomyController::class, 'search'])->name('taxonomy.search');

// Location Routes
Route::get('/locations', [LocationController::class, 'index'])->name('location.index');
Route::get('/locations/tree', [LocationController::class, 'tree'])->name('location.tree');
Route::get('/locations/api', [LocationController::class, 'apiTree'])->name('location.api');
Route::get('/locations/search', [LocationController::class, 'search'])->name('location.search');

// Location standalone route (before category routes)
Route::get('/location/{slug}', [LocationController::class, 'show'])->name('location.show');
Route::get('/location/{citySlug}/{districtSlug}', [LocationController::class, 'district'])->name('location.district');

// Content by direct slug (/p/slug)
Route::get('/p/{slug}', [ContentController::class, 'showBySlug'])
    ->where('slug', '[^/]+')
    ->name('content.by-slug');

// Taxonomy + Location (e.g., /kombi/istanbul)
Route::get('/{category}/{locationSlug}', [ContentController::class, 'index'])
    ->where('category', '[^/]+')
    ->where('locationSlug', '[^/]+')
    ->name('taxonomy.location');

// Taxonomy only (e.g., /kombi)
Route::get('/{category}', [ContentController::class, 'taxonomyIndex'])
    ->where('category', '[^/]+')
    ->name('taxonomy.category');

// Full content route – category / location / slug (MUST be last)
Route::get('/{category}/{locationSlug}/{slug}', [ContentController::class, 'show'])
    ->where('category', '[^/]+')
    ->where('locationSlug', '[^/]+')
    ->where('slug', '[^/]+')
    ->name('content.show');

});