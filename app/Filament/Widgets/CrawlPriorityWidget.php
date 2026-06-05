<?php

namespace App\Filament\Widgets;

use App\Services\CrawlPriorityEngine;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

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

        return [
            Stat::make('Avg Priority Score', number_format($avg, 1))
                ->description('İndeksleme önceliği')
                ->icon('heroicon-o-chart-bar')
                ->color($avgColor),

            Stat::make('High Priority (70+)', number_format($high))
                ->description('Yüksek öncelikli')
                ->icon('heroicon-o-arrow-trending-up')
                ->color('success'),

            Stat::make('Medium (40-69)', number_format($medium))
                ->description('Orta öncelikli')
                ->icon('heroicon-o-minus')
                ->color('warning'),

            Stat::make('Low (< 40)', number_format($low))
                ->description('Düşük öncelikli')
                ->icon('heroicon-o-arrow-trending-down')
                ->color('gray'),

            Stat::make('Unscored', number_format($unscored))
                ->description('Puanlanmamış')
                ->icon('heroicon-o-question-mark-circle')
                ->color($unscored > 0 ? 'danger' : 'success'),
        ];
    }

}
