<?php

namespace App\Filament\Pages;

use Filament\Actions\StaticAction;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Actions\ActionGroup;
use Filament\Actions\Action;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Filament\Widgets\RecentContentWidget;
use App\Filament\Widgets\ContentByStatusWidget;
use App\Filament\Widgets\JobMonitorWidget;
use App\Filament\Widgets\MostProfitableLocationsWidget;
use App\Filament\Widgets\QueueSpeedWidget;
use App\Filament\Widgets\CrawlPriorityWidget;
use App\Filament\Widgets\IndexCoverageWidget;
use App\Filament\Widgets\AnomalyDashboardWidget;
use Illuminate\Support\Facades\Artisan;

class Dashboard extends BaseDashboard
{
    protected function getHeaderWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
            IndexCoverageWidget::class,
            CrawlPriorityWidget::class,
            MostProfitableLocationsWidget::class,
            QueueSpeedWidget::class,
        ];
    }

    public function getWidgets(): array
    {
        return [
            JobMonitorWidget::class,
            ContentByStatusWidget::class,
            RecentContentWidget::class,
            AnomalyDashboardWidget::class,
        ];
    }

    protected function getActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('clear_cache')
                    ->label('🔥 Clear System Cache')
                    ->icon('heroicon-o-archive-box-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function () {
                        Artisan::call('cache:clear');
                        $this->notify('success', 'System cache cleared successfully!');
                    }),
                Action::make('rebuild_sitemaps')
                    ->label('📈 Rebuild Sitemaps')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function () {
                        \App\Jobs\SitemapRefreshJob::dispatch();
                        $this->notify('info', 'Sitemap refresh job dispatched!');
                    }),
                Action::make('clear_failed_jobs')
                    ->label('🗑️ Flush Failed Jobs')
                    ->icon('heroicon-o-trash')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->action(function () {
                        \Illuminate\Support\Facades\DB::table('failed_jobs')->delete();
                        $this->notify('success', 'Failed jobs cleared!');
                    }),
                Action::make('optimize_database')
                    ->label('⚙️ Optimize Database')
                    ->icon('heroicon-o-circle-stack')
                    ->color('info')
                    ->requiresConfirmation()
                    ->action(function () {
                        Artisan::call('optimize:clear');
                        $this->notify('success', 'Database optimized!');
                    }),
                Action::make('recalculate_priority')
                    ->label('📊 Recalculate Crawl Priority')
                    ->icon('heroicon-o-chart-bar')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->action(function () {
                        \App\Jobs\RecalculatePriorityJob::dispatch();
                        $this->notify('info', 'RecalculatePriorityJob dispatched!');
                    }),
                Action::make('sync_gsc')
                    ->label('🔍 Sync GSC Telemetry')
                    ->icon('heroicon-o-globe-alt')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function () {
                        \App\Jobs\SyncGscDataJob::dispatch();
                        $this->notify('info', 'SyncGscDataJob dispatched!');
                    }),
                Action::make('detect_anomalies')
                    ->label('🚨 Detect Anomalies')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function () {
                        \App\Jobs\DetectAnomaliesJob::dispatch();
                        $this->notify('info', 'DetectAnomaliesJob dispatched!');
                    }),
                Action::make('feedback_loop')
                    ->label('🔄 Telemetry Feedback Loop')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->requiresConfirmation()
                    ->action(function () {
                        \App\Jobs\TelemetryFeedbackLoopJob::dispatch();
                        $this->notify('info', 'TelemetryFeedbackLoopJob dispatched!');
                    }),
            ])
                ->label('⚡ GLOBAL OPERATIONS')
                ->icon('heroicon-o-cog-6-tooth')
                ->color('gray')
                ->button(),
        ];
    }
}