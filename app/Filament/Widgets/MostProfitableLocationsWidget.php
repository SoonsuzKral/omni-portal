<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class MostProfitableLocationsWidget extends BaseWidget
{
    protected int $columns = 3;

    protected function getStats(): array
    {
        $topLocations = DB::table('content_nodes')
            ->select(
                'locations.id',
                'locations.name',
                DB::raw('SUM(content_nodes.page_views) as total_views'),
                DB::raw('SUM(CASE WHEN content_nodes.is_restricted_content = 1 THEN content_nodes.page_views ELSE 0 END) as restricted_views')
            )
            ->join('locations', 'content_nodes.location_id', '=', 'locations.id')
            ->whereNotNull('content_nodes.publish_date')
            ->groupBy('locations.id', 'locations.name')
            ->orderByDesc('total_views')
            ->limit(6)
            ->get()
            ->map(function ($loc) {
                $loc->revenue_score = ($loc->restricted_views * 0.7) + ($loc->total_views * 0.3);
                return $loc;
            })
            ->sortByDesc('revenue_score')
            ->take(6)
            ->values();

        $stats = [];
        foreach ($topLocations as $index => $loc) {
            $stats[] = Stat::make('#' . ($index + 1) . ' ' . $loc->name, number_format($loc->total_views) . ' views')
                ->description('Revenue: $' . number_format($loc->revenue_score / 1000, 1) . 'K')
                ->color($index < 3 ? 'success' : 'gray');
        }

        return $stats;
    }
}