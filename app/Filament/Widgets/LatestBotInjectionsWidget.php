<?php

namespace App\Filament\Widgets;

use App\Models\ContentNode;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class LatestBotInjectionsWidget extends Widget
{
    protected static string $view = 'filament.widgets.latest-bot-injections-widget';

    public static function canView(): bool
    {
        return true;
    }

    public function getRecentInjections(): array
    {
        $recentContent = ContentNode::with(['location', 'taxonomy'])
            ->orderByDesc('created_at')
            ->limit(8)
            ->get()
            ->map(function ($node) {
                $locationType = 'city';
                if ($node->location) {
                    $locationType = str_contains($node->location->name, 'ilçe') || str_contains($node->location->name, 'District') 
                        ? 'district' 
                        : (strlen($node->location->name) > 15 ? 'district' : 'city');
                }

                return [
                    'id' => $node->id,
                    'title' => $node->seo_title,
                    'location' => $node->location?->name ?? 'N/A',
                    'location_type' => $locationType,
                    'taxonomy' => $node->taxonomy?->name ?? 'N/A',
                    'created_at' => $node->created_at,
                    'is_restricted' => $node->is_restricted_content,
                ];
            })
            ->toArray();

        return $recentContent;
    }
}