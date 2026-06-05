<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ResponseCache
{
    protected array $except = [
        'api/*',
        'admin/*',
        'filament/*',
        'lang/*',
    ];

    protected int $ttl = 300;

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldNotCache($request) || app()->environment('local')) {
            return $next($request);
        }

        $cacheKey = $this->generateCacheKey($request);

        if (Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);

            if ($cached) {
                Log::debug('Cache hit', ['key' => $cacheKey]);
                return response($cached['content'], $cached['status'])
                    ->withHeaders($cached['headers']);
            }
        }

        $response = $next($request);

        if ($this->shouldCacheResponse($response)) {
            $content = $response->getContent();
            $status = $response->getStatusCode();
            $headers = $response->headers->all();

            Cache::put($cacheKey, [
                'content' => $content,
                'status' => $status,
                'headers' => $headers,
            ], $this->ttl);

            Log::debug('Response cached', ['key' => $cacheKey, 'ttl' => $this->ttl]);
        }

        return $response;
    }

    protected function shouldNotCache(Request $request): bool
    {
        foreach ($this->except as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }

        if ($request->method() !== 'GET') {
            return true;
        }

        if ($request->has('no_cache')) {
            return true;
        }

        return false;
    }

    protected function generateCacheKey(Request $request): string
    {
        $path = $request->getPathInfo();
        $query = $request->getQueryString() ?? '';
        $locale = app()->getLocale();
        $testMode = Cache::remember('ads_test_mode', 60, function () {
            return \App\Models\LiveDataVault::where('key', 'ads_test_mode')->value('value') === '1';
        }) ? '1' : '0';

        return 'response:' . md5($locale . $path . $query . $testMode);
    }

    protected function shouldCacheResponse(Response $response): bool
    {
        if ($response->getStatusCode() !== 200) {
            return false;
        }

        $contentType = $response->headers->get('Content-Type', '');

        if (!str_contains($contentType, 'text/html')) {
            return false;
        }

        return true;
    }
}