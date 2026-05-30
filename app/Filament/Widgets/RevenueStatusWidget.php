<?php

namespace App\Filament\Widgets;

use App\Models\ContentNode;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class RevenueStatusWidget extends Widget
{
    protected static string $view = 'filament.widgets.revenue-status-widget';

    protected int $columns = 2;

    public static function canView(): bool
    {
        return true;
    }

    public function getRevenueData(): array
    {
        $totalViews = ContentNode::sum('page_views');
        $restrictedViews = ContentNode::where('is_restricted_content', true)->sum('page_views');
        $safeViews = ContentNode::where('is_restricted_content', false)->sum('page_views');

        $safeCPC = 0.02;
        $restrictedCPC = 0.08;

        $safeEstimatedRevenue = ($safeViews / 1000) * $safeCPC * 1000;
        $restrictedEstimatedRevenue = ($restrictedViews / 1000) * $restrictedCPC * 1000;

        return [
            'safe' => [
                'views' => $safeViews,
                'revenue' => $safeEstimatedRevenue,
                'cpc' => $safeCPC,
            ],
            'restricted' => [
                'views' => $restrictedViews,
                'revenue' => $restrictedEstimatedRevenue,
                'cpc' => $restrictedCPC,
            ],
            'total_views' => $totalViews,
            'total_revenue' => $safeEstimatedRevenue + $restrictedEstimatedRevenue,
        ];
    }

    public function getTopLocations(): array
    {
        return DB::table('content_nodes')
            ->join('locations', 'content_nodes.location_id', '=', 'locations.id')
            ->select('locations.name', DB::raw('SUM(content_nodes.page_views) as total_views'))
            ->whereNotNull('content_nodes.location_id')
            ->groupBy('locations.id', 'locations.name')
            ->orderByDesc('total_views')
            ->limit(5)
            ->get()
            ->toArray();
    }
}