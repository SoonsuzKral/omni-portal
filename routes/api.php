<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\IngestController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ResourceController;
use App\Http\Controllers\Api\KeywordOrchestraController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['sql.protect', 'rate.limit.enhanced'])->group(function () {

// Public endpoints (no auth required)
Route::get('/health', [IngestController::class, 'health']);
Route::get('/v1/ingest/status', [IngestController::class, 'status']);
Route::get('/v1/stats', [ResourceController::class, 'stats']);
Route::get('/v1/search/suggestions', [\App\Http\Controllers\ContentController::class, 'suggestions']);

// Auth endpoints (no auth required - for token generation)
Route::post('/v1/auth/token', [AuthController::class, 'token'])
    ->middleware('throttle:login');

// Protected endpoints (require Sanctum token)
Route::middleware(['api.token', 'throttle:60,1'])->group(function () {
    // Ingest
    Route::post('/v1/ingest', [IngestController::class, 'store']);

    // Auth management
    Route::get('/v1/auth/me', [AuthController::class, 'me']);
    Route::delete('/v1/auth/revoke', [AuthController::class, 'revoke']);
    Route::delete('/v1/auth/revoke-all', [AuthController::class, 'revokeAll']);

    // Taxonomies CRUD
    Route::get('/v1/taxonomies', [ResourceController::class, 'taxonomies']);
    Route::post('/v1/taxonomies', [ResourceController::class, 'createTaxonomy']);
    Route::get('/v1/taxonomies/{slug}', [ResourceController::class, 'showTaxonomy']);
    Route::put('/v1/taxonomies/{slug}', [ResourceController::class, 'updateTaxonomy']);
    Route::delete('/v1/taxonomies/{slug}', [ResourceController::class, 'deleteTaxonomy']);

    // Locations CRUD
    Route::get('/v1/locations', [ResourceController::class, 'locations']);
    Route::post('/v1/locations', [ResourceController::class, 'createLocation']);
    Route::get('/v1/locations/{slug}', [ResourceController::class, 'showLocation']);
    Route::put('/v1/locations/{slug}', [ResourceController::class, 'updateLocation']);
    Route::delete('/v1/locations/{slug}', [ResourceController::class, 'deleteLocation']);

    // Content Nodes CRUD
    Route::get('/v1/content-nodes', [ResourceController::class, 'contentNodes']);
    Route::get('/v1/content-nodes/{slug}', [ResourceController::class, 'showContentNode']);
    Route::put('/v1/content-nodes/{slug}', [ResourceController::class, 'updateContentNode']);
    Route::delete('/v1/content-nodes/{slug}', [ResourceController::class, 'deleteContentNode']);

    // Keywords CRUD
    Route::get('/v1/keywords', [ResourceController::class, 'keywords']);
    Route::post('/v1/keywords', [ResourceController::class, 'createKeyword']);
    Route::get('/v1/keywords/{id}', [ResourceController::class, 'showKeyword']);
    Route::put('/v1/keywords/{id}', [ResourceController::class, 'updateKeyword']);
    Route::delete('/v1/keywords/{id}', [ResourceController::class, 'deleteKeyword']);

    // Live Data
    Route::get('/v1/live-data', [ResourceController::class, 'liveData']);
    Route::post('/v1/live-data', [ResourceController::class, 'upsertLiveData']);

    // Post Templates
    Route::get('/v1/post-templates', [ResourceController::class, 'postTemplates']);

    // Export
    Route::post('/v1/export', [ResourceController::class, 'export']);

    // Keyword Orchestra (Python script integration)
    Route::post('/v1/orchestra/keywords', [KeywordOrchestraController::class, 'importKeywords']);
    Route::get('/v1/orchestra/sync-countries', [KeywordOrchestraController::class, 'syncCountries']);
    Route::get('/v1/orchestra/status', [KeywordOrchestraController::class, 'getStatus']);
});

});