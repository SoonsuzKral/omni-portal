<?php

namespace App\Helpers;

use App\Models\Taxonomy;
use App\Models\Location;
use App\Models\ContentNode;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SlugGenerator
{
    /**
     * Generate a unique slug for content nodes.
     * Handles duplicates by appending random number.
     */
    public static function generateUnique(string $baseSlug, ?int $taxonomyId = null, ?int $locationId = null, string $table = 'content_nodes'): string
    {
        $slug = Str::slug($baseSlug);
        $originalSlug = $slug;
        $counter = 0;

        while (self::slugExists($slug, $table, $taxonomyId, $locationId)) {
            $counter++;
            // Append random 3-digit number for better distribution
            $slug = $originalSlug . '-' . rand(100, 999);

            // Safety limit - if we can't find unique after 100 tries, use timestamp
            if ($counter >= 100) {
                $slug = $originalSlug . '-' . time() . '-' . rand(10, 99);
                break;
            }
        }

        return $slug;
    }

    /**
     * Check if slug already exists in table.
     */
    protected static function slugExists(string $slug, string $table, ?int $taxonomyId = null, ?int $locationId = null): bool
    {
        $query = DB::table($table)->where('slug', $slug);

        // For content_nodes, also check taxonomy_id and location_id combination
        if ($table === 'content_nodes' && $taxonomyId && $locationId) {
            $query->where('taxonomy_id', $taxonomyId)
                  ->where('location_id', $locationId);
        }

        return $query->exists();
    }

    /**
     * Generate slug from title with SEO best practices.
     */
    public static function fromTitle(string $title, ?int $maxLength = 60): string
    {
        // Convert to lowercase, slugify, and truncate
        $slug = Str::slug($title);

        if ($maxLength && strlen($slug) > $maxLength) {
            $slug = substr($slug, 0, $maxLength);
            // Remove trailing hyphen if truncated mid-word
            $slug = rtrim($slug, '-');
        }

        return $slug;
    }

    /**
     * Full generation: title -> slug -> ensure unique.
     */
    public static function make(string $title, ?int $taxonomyId = null, ?int $locationId = null): string
    {
        $baseSlug = self::fromTitle($title);
        return self::generateUnique($baseSlug, $taxonomyId, $locationId);
    }

    /**
     * Generate a simple keyword slug (no uniqueness check needed).
     */
    public static function generateKeywordSlug(string $keyword): string
    {
        return Str::slug($keyword);
    }

    /**
     * Batch generate unique slugs for multiple items.
     * Optimized for bulk imports.
     */
    public static function makeBatch(array $items): array
    {
        $results = [];

        foreach ($items as $item) {
            $results[] = [
                'original' => $item['title'],
                'slug' => self::make(
                    $item['title'],
                    $item['taxonomy_id'] ?? null,
                    $item['location_id'] ?? null
                ),
            ];
        }

        return $results;
    }
}