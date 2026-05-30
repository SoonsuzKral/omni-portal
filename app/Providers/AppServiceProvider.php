<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\FilamentManager;
use Filament\Facades\Filament;
use App\Models\ContentNode;
use App\Observers\ContentNodeObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\IndexingService::class);
        $this->app->singleton(\App\Services\CrawlPriorityEngine::class);
        $this->app->singleton(\App\Services\GoogleSearchConsoleService::class);
        $this->app->singleton(\App\Services\SearchTelemetryEngine::class);
        $this->app->singleton(\App\Services\AdaptivePriorityEngine::class);
        $this->app->singleton(\App\Services\IndexCoverageMonitor::class);
        $this->app->singleton(\App\Services\AnomalyDetectionEngine::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ContentNode::observe(ContentNodeObserver::class);
    }
}