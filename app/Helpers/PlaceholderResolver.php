<?php

namespace App\Helpers;

use App\Models\Location;
use App\Models\Taxonomy;
use App\Services\ExternalDataService;
use Illuminate\Support\Facades\Cache;

class PlaceholderResolver
{
    /**
     * Resolve placeholders and spintax.
     *
     * Supported placeholders:
     * - {city}, {neighborhood} - Location-based
     * - {usd}, {gold}, {weather_istanbul} - External data
     * - {custom_key} - LiveDataVault values
     *
     * Supports spintax patterns like {Good|Great|Amazing}
     */
    public static function resolve(string $content, ?Location $location = null, ?Taxonomy $taxonomy = null): string
    {
        $locationId = $location?->id;
        $taxonomyId = $taxonomy?->id;

        // 1. Simple location-based replacements
        if ($location) {
            $content = str_replace('{city}', $location->name, $content);

            // Try to get child neighborhood, fallback to location name
            $neighborhood = $location->children()->first();
            $content = str_replace('{neighborhood}', $neighborhood?->name ?? $location->name, $content);

            // Parent location (district/city)
            if ($location->parent) {
                $content = str_replace('{district}', $location->parent->name, $content);
            }
        }

        // 2. Taxonomy placeholders
        if ($taxonomy) {
            $content = str_replace('{category}', $taxonomy->name, $content);
            $content = str_replace('{taxonomy}', $taxonomy->name, $content);
        }

        // 3. External data service (Redis-cached)
        $externalService = app(ExternalDataService::class);
        $content = $externalService->resolvePlaceholders($content, $locationId, $taxonomyId);

        // 4. Spintax resolution – replace patterns like {option1|option2|option3}
        $content = preg_replace_callback('/\{([^{}]*)\}/', function ($matches) {
            // Skip already resolved placeholders
            $inner = $matches[1];
            if (strpos($inner, '|') === false) {
                return $matches[0];
            }
            $options = explode('|', $inner);
            return $options[array_rand($options)];
        }, $content);

        return $content;
    }

    /**
     * Generate a meta description from resolved content.
     */
    public static function metaDescription(string $resolvedContent, int $length = 160): string
    {
        $desc = trim(preg_replace('/\s+/', ' ', strip_tags($resolvedContent)));
        return mb_substr($desc, 0, $length);
    }

    /**
     * Clear placeholder cache (for admin refresh).
     */
    public static function clearCache(): void
    {
        app(ExternalDataService::class)->clearCache();
    }
}
?>