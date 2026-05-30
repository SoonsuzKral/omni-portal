<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PingService
{
    const PING_CACHE_TTL = 1800;
    const CACHE_KEY = 'last_search_engine_ping';

    public function pingSearchEngines(): array
    {
        $results = [
            'google' => false,
            'bing' => false,
        ];

        $sitemapUrl = url('/sitemap.xml');

        try {
            $googleResponse = Http::get('https://www.google.com/ping', [
                'sitemap' => $sitemapUrl,
            ]);
            $results['google'] = $googleResponse->successful();
        } catch (\Exception $e) {
            Log::warning("PingService: Google ping failed", ['error' => $e->getMessage()]);
        }

        try {
            $bingResponse = Http::get('https://www.bing.com/ping', [
                'sitemap' => $sitemapUrl,
            ]);
            $results['bing'] = $bingResponse->successful();
        } catch (\Exception $e) {
            Log::warning("PingService: Bing ping failed", ['error' => $e->getMessage()]);
        }

        Cache::put(self::CACHE_KEY, $results, self::PING_CACHE_TTL);

        Log::info('PingService: Search engines pinged', $results);

        return $results;
    }

    public function getLastPingResult(): ?array
    {
        return Cache::get(self::CACHE_KEY);
    }
}
