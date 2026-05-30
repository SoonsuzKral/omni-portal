<?php

namespace App\Filament\Widgets;

use App\Models\ContentNode;
use App\Models\AnomalyDetection;
use App\Services\IndexCoverageMonitor;
use App\Services\SearchTelemetryEngine;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class IndexCoverageWidget extends BaseWidget
{
    protected int $columns = 6;

    protected ?string $heading = 'Search Console Telemetry';

    protected function getStats(): array
    {
        $engine = app(SearchTelemetryEngine::class);
        $coverageMonitor = app(IndexCoverageMonitor::class);

        $stats = $engine->getAggregateStats();
        $ratio = $coverageMonitor->getSubmittedVsIndexedRatio();
        $crawlEff = $coverageMonitor->getCrawlEfficiency();
        $sitemapEff = $coverageMonitor->getSitemapEfficiency();

        $activeAnomalies = AnomalyDetection::active()->count();
        $criticalAnomalies = AnomalyDetection::active()->critical()->count();

        $coverageColor = $stats['coverage_percentage'] >= 80 ? 'success' : ($stats['coverage_percentage'] >= 50 ? 'warning' : 'danger');
        $ratioColor = $ratio >= 80 ? 'success' : ($ratio >= 50 ? 'warning' : 'danger');

        return [
            Stat::make('Index Coverage', number_format($stats['coverage_percentage'], 1) . '%')
                ->description("{$stats['indexed_urls']} / {$stats['total_urls']} URLs")
                ->icon('heroicon-o-document-text')
                ->color($coverageColor),

            Stat::make('Submitted vs Indexed', number_format($ratio, 1) . '%')
                ->icon('heroicon-o-check-badge')
                ->color($ratioColor),

            Stat::make('Avg Indexing Latency', number_format($stats['avg_index_latency_minutes'], 0) . ' min')
                ->icon('heroicon-o-clock')
                ->color('info'),

            Stat::make('Crawl Efficiency', number_format($crawlEff, 1) . '%')
                ->icon('heroicon-o-globe-alt')
                ->color($crawlEff >= 90 ? 'success' : 'warning'),

            Stat::make('Sitemap Efficiency', number_format($sitemapEff, 1) . '%')
                ->icon('heroicon-o-sitemap')
                ->color($sitemapEff >= 80 ? 'success' : 'warning'),

            Stat::make('Active Anomalies', number_format($activeAnomalies))
                ->description("{$criticalAnomalies} critical")
                ->icon('heroicon-o-exclamation-triangle')
                ->color($criticalAnomalies > 0 ? 'danger' : ($activeAnomalies > 0 ? 'warning' : 'success')),
        ];
    }
}
