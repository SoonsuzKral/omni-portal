<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'sql.protect' => \App\Http\Middleware\SqlInjectionProtection::class,
            'rate.limit.enhanced' => \App\Http\Middleware\EnhancedRateLimiter::class,
            'admin.ip' => \App\Http\Middleware\AdminIpWhitelist::class,
            'response.cache' => \App\Http\Middleware\ResponseCache::class,
            'traffic.leak.prevention' => \App\Http\Middleware\TrafficLeakPrevention::class,
            'api.token' => \App\Http\Middleware\ApiTokenAuth::class,
        ]);

        $middleware->web(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
            \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\TrafficLeakPrevention::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\ResponseCache::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withSchedule(function (Schedule $schedule): void {
        // Daily market data update at 6:00 AM
        $schedule->command('market:update')
            ->dailyAt('06:00')
            ->withoutOverlapping()
            ->runInBackground();

        // Sitemap refresh every 6 hours
        $schedule->command('sitemap:rebuild')
            ->everySixHours()
            ->withoutOverlapping()
            ->runInBackground();

        // Database cleanup - remove old logs (weekly)
        $schedule->command('model:prune')
            ->weekly()
            ->onOneServer();
    })
    ->create();
