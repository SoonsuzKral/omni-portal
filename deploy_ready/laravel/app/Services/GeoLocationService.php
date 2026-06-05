<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GeoLocationService
{
    protected string $apiUrl = 'http://ip-api.com/json/';

    protected array $countryLocaleMap = [
        'TR' => 'tr',
        'DE' => 'de',
        'FR' => 'fr',
        'ES' => 'es',
        'IT' => 'it',
        'NL' => 'nl',
        'BE' => 'nl',
        'AT' => 'de',
        'CH' => 'de',
        'SE' => 'sv',
        'NO' => 'no',
        'DK' => 'da',
        'FI' => 'fi',
        'PL' => 'pl',
        'CZ' => 'cs',
        'SK' => 'sk',
        'HU' => 'hu',
        'RO' => 'ro',
        'BG' => 'bg',
        'GR' => 'el',
        'PT' => 'pt',
        'RU' => 'ru',
        'UA' => 'uk',
        'SA' => 'ar',
        'AE' => 'ar',
        'QA' => 'ar',
        'KW' => 'ar',
        'BH' => 'ar',
        'OM' => 'ar',
        'EG' => 'ar',
        'IQ' => 'ar',
        'SY' => 'ar',
        'JO' => 'ar',
        'LB' => 'ar',
        'YE' => 'ar',
        'LY' => 'ar',
        'DZ' => 'ar',
        'MA' => 'ar',
        'TN' => 'ar',
        'GB' => 'en',
        'US' => 'en',
        'CA' => 'en',
        'AU' => 'en',
        'NZ' => 'en',
        'IE' => 'en',
        'IN' => 'en',
    ];

    public function detectLocale(?string $ip = null): ?string
    {
        $ip = $ip ?? request()->ip();

        if ($this->isPrivateIp($ip)) {
            return null;
        }

        return Cache::remember('geo_locale_' . md5($ip), 86400, function () use ($ip) {
            try {
                $response = Http::timeout(3)->get($this->apiUrl . $ip, [
                    'fields' => 'countryCode',
                ]);

                if ($response->successful() && $response->json('status') === 'success') {
                    $countryCode = strtoupper($response->json('countryCode', ''));
                    return $this->countryLocaleMap[$countryCode] ?? null;
                }
            } catch (\Throwable $e) {
                // Silently fail - geolocation is optional
            }

            return null;
        });
    }

    protected function isPrivateIp(string $ip): bool
    {
        $filtered = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
        return $filtered === false;
    }
}
