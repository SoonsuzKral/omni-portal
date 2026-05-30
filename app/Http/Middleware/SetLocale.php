<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use App\Services\GeoLocationService;

class SetLocale
{
    protected array $supportedLocales = ['tr', 'en', 'ru', 'ar'];

    public function handle(Request $request, Closure $next)
    {
        $locale = $this->resolveLocale($request);

        if (in_array(strtolower($locale), $this->supportedLocales)) {
            App::setLocale(strtolower($locale));
        } else {
            App::setLocale('tr');
        }

        return $next($request);
    }

    protected function resolveLocale(Request $request): string
    {
        $locale = session('locale');

        if ($locale && in_array(strtolower($locale), $this->supportedLocales)) {
            return $locale;
        }

        $locale = $request->cookie('app_locale');

        if ($locale && in_array(strtolower($locale), $this->supportedLocales)) {
            session(['locale' => $locale]);
            return $locale;
        }

        $browserLocale = $this->detectBrowserLocale($request);

        if ($browserLocale && in_array(strtolower($browserLocale), $this->supportedLocales)) {
            session(['locale' => $browserLocale]);
            Cookie::queue('app_locale', $browserLocale, 43200);
            return $browserLocale;
        }

        try {
            $geoLocale = app(GeoLocationService::class)->detectLocale($request->ip());
            if ($geoLocale && in_array(strtolower($geoLocale), $this->supportedLocales)) {
                session(['locale' => $geoLocale]);
                Cookie::queue('app_locale', $geoLocale, 43200);
                return $geoLocale;
            }
        } catch (\Throwable $e) {
            // GeoLocation is optional
        }

        return 'tr';
    }

    protected function detectBrowserLocale(Request $request): ?string
    {
        $acceptLanguage = $request->server('HTTP_ACCEPT_LANGUAGE');

        if (!$acceptLanguage) {
            return null;
        }

        preg_match_all('/([a-z]{2})(?:-[A-Z]{2})?(?:;q=[0-9.]+)?/i', $acceptLanguage, $matches);

        if (empty($matches[1])) {
            return null;
        }

        $locales = array_unique(array_map('strtolower', $matches[1]));

        foreach ($locales as $locale) {
            if (in_array($locale, $this->supportedLocales)) {
                return $locale;
            }
        }

        return null;
    }
}
