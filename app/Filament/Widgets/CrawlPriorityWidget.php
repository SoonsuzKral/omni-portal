<?php

namespace App\Filament\Widgets;

use App\Models\ContentNode;
use App\Services\CrawlPriorityEngine;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class CrawlPriorityWidget extends BaseWidget
{
    protected int $columns = 5;

    protected ?string $heading = 'Crawl Priority Engine';

    protected function getStats(): array
    {
        $engine = app(CrawlPriorityEngine::class);
        $stats = $engine->getAggregateStats();

        $avg = $stats['average_score'];
        $high = $stats['distribution']['high_priority'];
        $medium = $stats['distribution']['medium_priority'];
        $low = $stats['distribution']['low_priority'];
        $unscored = $stats['distribution']['unscored'];

        $avgColor = $avg >= 50 ? 'success' : ($avg >= 25 ? 'warning' : 'danger');

        $avgChange = $this->getAverageChange();

        return [
            Stat::make('Avg Priority Score', number_format($avg, 1))
                ->description($avgChange >= 0 ? "▲ {$avgChange} vs yesterday" : "▼ {$avgChange} vs yesterday")
                ->icon('heroicon-o-chart-bar')
                ->color($avgColor),

            Stat::make('High Priority (70+)', number_format($high))
                ->description(fn () => $stats['total_scored'] > 0
                    ? round(($high / max(1, $stats['total_scored'])) * 100, 1) . '% of scored'
                    : 'No scored content')
                ->icon('heroicon-o-arrow-trending-up')
                ->color('success'),

            Stat::make('Medium (40-69)', number_format($medium))
                ->icon('heroicon-o-minus')
                ->color('warning'),

            Stat::make('Low (< 40)', number_format($low))
                ->icon('heroicon-o-arrow-trending-down')
                ->color('gray'),

            Stat::make('Unscored', number_format($unscored))
                ->description(fn () => $unscored > 0
                    ? 'Run php artisan seo:recalculate-priority'
                    : 'All scored')
                ->icon('heroicon-o-question-mark-circle')
                ->color($unscored > 0 ? 'danger' : 'success'),
        ];
    }

    private function getAverageChange(): float
    {
        $today = ContentNode::whereNotNull('crawl_priority_score')
            ->whereDate('updated_at', today())
            ->avg('crawl_priority_score');

        $yesterday = ContentNode::whereNotNull('crawl_priority_score')
            ->whereDate('updated_at', today()->subDay())
            ->avg('crawl_priority_score');

        return round(($today ?? 0) - ($yesterday ?? 0), 1);
    }
}
