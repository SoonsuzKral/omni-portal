<?php

namespace App\Services;

use App\Models\LiveDataVault;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ExternalDataService
{
    /**
     * Cache TTL in seconds (5 minutes for live data).
     */
    const CACHE_TTL = 300;

    /**
     * External API endpoints for live data (fallback).
     */
    protected array $externalApis = [
        'gold' => 'https://api.exchangerate.host/latest?base=TRY&symbols=XAU',
        'usd' => 'https://api.exchangerate.host/latest?base=USD&symbols=TRY',
        'eur' => 'https://api.exchangerate.host/latest?base=EUR&symbols=TRY',
    ];

    /**
     * Resolve all placeholders in content.
     * Supports: {usd}, {gold}, {weather_istanbul}, {custom_key}, etc.
     */
    public function resolvePlaceholders(string $content, ?int $locationId = null, ?int $taxonomyId = null): string
    {
        // Find all placeholders in format {key}
        preg_match_all('/\{([^}]+)\}/', $content, $matches);

        if (empty($matches[1])) {
            return $content;
        }

        $uniqueKeys = array_unique($matches[1]);

        foreach ($uniqueKeys as $key) {
            $value = $this->resolvePlaceholder($key, $locationId, $taxonomyId);
            if ($value !== null) {
                $content = str_replace('{' . $key . '}', $value, $content);
            }
        }

        return $content;
    }

    /**
     * Resolve a single placeholder.
     */
    public function resolvePlaceholder(string $key, ?int $locationId = null, ?int $taxonomyId = null): ?string
    {
        $cacheKey = "placeholder:{$key}:" . ($locationId ?? 'global');

        // Try cache first
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $value = null;

        // 1. Check internal LiveDataVault first
        $vaultValue = $this->resolveFromVault($key);
        if ($vaultValue !== null) {
            $value = $vaultValue;
        }
        // 2. Try external API for known types
        elseif ($this->isExternalType($key)) {
            $value = $this->fetchExternalData($key, $locationId);
        }
        // 3. Try weather API for location-specific requests
        elseif (Str::startsWith($key, 'weather_')) {
            $value = $this->fetchWeatherData($key, $locationId);
        }

        // Cache the result
        if ($value !== null) {
            Cache::put($cacheKey, $value, self::CACHE_TTL);
        }

        return $value;
    }

    /**
     * Resolve from internal LiveDataVault table.
     */
    protected function resolveFromVault(string $key): ?string
    {
        // First try exact match
        $vault = LiveDataVault::where('key', $key)->first();
        if ($vault) {
            return $vault->value;
        }

        // Try fuzzy match for prefixed keys (e.g., "usd_try" matches "usd")
        $prefix = Str::before($key, '_');
        $vault = LiveDataVault::where('key', 'like', "{$prefix}%")->first();
        if ($vault) {
            return $vault->value;
        }

        return null;
    }

    /**
     * Check if key is a known external API type.
     */
    protected function isExternalType(string $key): bool
    {
        $knownTypes = array_keys($this->externalApis);
        $baseType = Str::before($key, '_');
        return in_array($baseType, $knownTypes);
    }

    /**
     * Fetch data from external APIs.
     */
    protected function fetchExternalData(string $key, ?int $locationId): ?string
    {
        $baseType = Str::before($key, '_');

        if (!isset($this->externalApis[$baseType])) {
            return null;
        }

        try {
            $response = Http::timeout(5)->get($this->externalApis[$baseType]);

            if ($response->successful()) {
                $data = $response->json();

                // Parse exchange rate response
                if (isset($data['rates'])) {
                    $rates = $data['rates'];
                    $target = Str::after($key, '_');

                    if ($target && isset($rates[$target])) {
                        return number_format($rates[$target], 2);
                    }

                    // Return first available rate
                    return !empty($rates) ? number_format(array_values($rates)[0], 2) : null;
                }
            }
        } catch (\Exception $e) {
            Log::warning("External API failed for {$key}", ['error' => $e->getMessage()]);
        }

        // Fallback to cached vault value
        return $this->resolveFromVault($key);
    }

    /**
     * Fetch weather data for location.
     */
    protected function fetchWeatherData(string $key, ?int $locationId): ?string
    {
        if (!$locationId) {
            return $this->resolveFromVault($key);
        }

        $location = \App\Models\Location::find($locationId);

        if (!$location) {
            return null;
        }

        // Try to get weather from vault with location fallback
        $citySlug = Str::after($key, 'weather_');
        $weatherKey = "weather_{$citySlug}_{$location->slug}";

        $value = $this->resolveFromVault($weatherKey);
        if ($value) {
            return $value;
        }

        // Try generic city weather
        return $this->resolveFromVault("weather_{$citySlug}");
    }

    /**
     * Clear placeholder cache (for admin refresh).
     */
    public function clearCache(): void
    {
        Cache::flush();
        Log::info('ExternalDataService cache cleared');
    }

    /**
     * Get all available placeholder keys for documentation.
     */
    public function getAvailablePlaceholders(): array
    {
        $vaultKeys = LiveDataVault::pluck('key')->toArray();

        return [
            'internal' => $vaultKeys,
            'external' => [
                '{usd}', '{usd_try}', '{eur}', '{eur_try}',
                '{gold}', '{weather_istanbul}', '{weather_ankara}',
            ],
            'patterns' => [
                '{key_name}' => 'Custom key from LiveDataVault',
                '{weather_city}' => 'Weather data for city',
                '{exchange_currency}' => 'Exchange rate',
            ],
        ];
    }
}