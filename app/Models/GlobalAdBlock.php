<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class GlobalAdBlock extends Model
{
    protected $fillable = ['name', 'script', 'network_type', 'active', 'position', 'forbidden_locations', 'cpm_note', 'taxonomy_id', 'is_global'];

    protected $casts = [
        'forbidden_locations' => 'array',
        'active' => 'boolean',
        'is_global' => 'boolean',
    ];

    const POSITIONS = [
        'head_script' => '🧠 Head Script (Auto Ads)',
        'ga4_tracking' => '📊 Google Analytics 4',
        'clarity_tracking' => '🔥 Microsoft Clarity',
        'global_header' => '🌐 Global Header (body start)',
        'global_footer' => '🌐 Global Footer (before footer)',
        'header_left' => '📌 Header Left',
        'header_right' => '📌 Header Right',
        'above_breadcrumb' => '🍞 Above Breadcrumb',
        'after_breadcrumb' => '🍞 After Breadcrumb',
        'under_h1' => '📰 Under H1',
        'top' => '🔼 Top - Before Content',
        'above_content' => '⬆ Above Content Section',
        'below_content' => '⬇ Below Content Section',
        'mid' => '📄 Mid - 3rd Paragraph',
        'mid_content_1' => '📄 Mid Content Slot 1',
        'mid_content_2' => '📄 Mid Content Slot 2',
        'mid_content_3' => '📄 Mid Content Slot 3',
        'bottom' => '🔽 Bottom - Content End',
        'sidebar' => '📱 Sidebar (general)',
        'left_sidebar_top' => '⬅ Left Sidebar Top',
        'left_sidebar_mid' => '⬅ Left Sidebar Middle',
        'left_sidebar_bottom' => '⬅ Left Sidebar Bottom',
        'right_sidebar_top' => '➡ Right Sidebar Top',
        'right_sidebar_mid' => '➡ Right Sidebar Middle',
        'right_sidebar_bottom' => '➡ Right Sidebar Bottom',
        'sticky_bottom' => '📌 Sticky Mobile Bottom',
        'sticky_left' => '📌 Sticky Left Side',
        'sticky_right' => '📌 Sticky Right Side',
        'location_top' => '📍 Location Page Top',
        'footer_left' => '🔻 Footer Left',
        'footer_right' => '🔻 Footer Right',
        'above_footer' => '🔺 Above Footer',
        'below_footer' => '🔻 Below Footer',
        'before_content_list' => '📋 Before Content List',
        'after_content_list' => '📋 After Content List',
        'between_pagination' => '📄 Between Pagination',
        'after_first_paragraph' => '📄 After 1st Paragraph',
        'after_second_paragraph' => '📄 After 2nd Paragraph',
        'below_title' => '📰 Below Title',
        'above_related' => '🔗 Above Related Content',
    ];

    public static function getPositionLabel(string $position): string
    {
        return self::POSITIONS[$position] ?? $position;
    }

    public static function getSidebarPositions(): array
    {
        return [
            'left_sidebar_top', 'left_sidebar_mid', 'left_sidebar_bottom',
            'right_sidebar_top', 'right_sidebar_mid', 'right_sidebar_bottom',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::flush());
        static::deleted(fn () => Cache::flush());
    }

    public function taxonomy(): BelongsTo
    {
        return $this->belongsTo(Taxonomy::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }

    public function scopeByPosition($query, string $position)
    {
        return $query->where('position', $position);
    }
}
