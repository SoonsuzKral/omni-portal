<?php

namespace App\Filament\Widgets;

use App\Models\ContentNode;
use App\Models\Taxonomy;
use App\Models\Location;
use App\Models\Keyword;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StatsOverviewWidget extends BaseWidget
{
    protected int $columns = 6;

    protected function getStats(): array
    {
        $safeContent = ContentNode::where('is_restricted_content', false)->count();
        $restricted = ContentNode::where('is_restricted_content', true)->count();
        $locations = Location::count();
        $taxonomies = Taxonomy::count();
        $keywords = Keyword::count();
        
        $activeIngest = DB::table('jobs')
            ->where('payload', 'like', '%Ingest%')
            ->count();

        return [
            Stat::make('🟢 Safe Content', number_format($safeContent))
                ->description('Ad policy compliant')
                ->icon('heroicon-o-shield-check')
                ->color('success'),

            Stat::make('🔴 Restricted Content', number_format($restricted))
                ->description('Limited ads')
                ->icon('heroicon-o-shield-exclamation')
                ->color('danger'),

            Stat::make('📍 Locations', number_format($locations))
                ->description('Cities & districts')
                ->icon('heroicon-o-map-pin')
                ->color('info'),

            Stat::make('📂 Taxonomies', number_format($taxonomies))
                ->description('Active categories')
                ->icon('heroicon-o-folder')
                ->color('warning'),

            Stat::make('✨ Keywords', number_format($keywords))
                ->description('Being tracked')
                ->icon('heroicon-o-sparkles')
                ->color('primary'),

            Stat::make('📡 API Streams', $activeIngest)
                ->description($activeIngest > 0 ? 'Ingest active' : 'Idle')
                ->icon('heroicon-o-signal')
                ->color($activeIngest > 0 ? 'success' : 'gray'),
        ];
    }
}