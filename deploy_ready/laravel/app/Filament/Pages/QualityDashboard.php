<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\Quality\QualityStatsOverviewWidget;
use App\Filament\Widgets\Quality\EeatMetricsWidget;
use App\Filament\Widgets\Quality\SemanticUniquenessWidget;
use App\Filament\Widgets\Quality\SpamRiskWidget;
use App\Filament\Widgets\Quality\EngagementQualityWidget;
use App\Filament\Widgets\Quality\AuthorityClustersWidget;
use App\Filament\Widgets\Quality\ContentDepthWidget;
use App\Filament\Widgets\Quality\EntityCoverageWidget;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;

class QualityDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $navigationLabel = 'Quality Dashboard';
    protected static ?string $title = 'AI Quality & Entity Authority Engine';
    protected static ?string $slug = 'quality';
    protected static ?int $navigationSort = 1;

    protected function getHeaderWidgets(): array
    {
        return [
            QualityStatsOverviewWidget::class,
            EeatMetricsWidget::class,
            SemanticUniquenessWidget::class,
            AuthorityClustersWidget::class,
        ];
    }

    public function getWidgets(): array
    {
        return [
            SpamRiskWidget::class,
            EngagementQualityWidget::class,
            ContentDepthWidget::class,
            EntityCoverageWidget::class,
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function getNavigationGroup(): ?string
    {
        return '🤖 AI QUALITY ENGINE';
    }

    protected function getActions(): array
    {
        return [
            \Filament\Actions\ActionGroup::make([
                \Filament\Actions\Action::make('analyze_quality')
                    ->label('Analyze All Content')
                    ->icon('heroicon-o-document-magnifying-glass')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->action(function () {
                        \App\Jobs\ProcessQualityBatchJob::dispatch(
                            \App\Models\ContentNode::whereNotNull('body_content')
                                ->whereNull('last_quality_analyzed_at')
                                ->pluck('id')->toArray()
                        );
                        $this->notify('success', 'Quality analysis dispatched for unscored content!');
                    }),
                \Filament\Actions\Action::make('rebuild_graph')
                    ->label('Rebuild Entity Graph')
                    ->icon('heroicon-o-share-nodes')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function () {
                        \App\Jobs\RebuildEntityGraphJob::dispatch();
                        $this->notify('info', 'Entity graph rebuild dispatched!');
                    }),
                \Filament\Actions\Action::make('detect_spam')
                    ->label('Detect Spam Risk')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function () {
                        \App\Models\ContentNode::whereNotNull('body_content')->chunk(100, function ($contents) {
                            foreach ($contents as $content) {
                                \App\Jobs\DetectSpamRiskJob::dispatch($content);
                            }
                        });
                        $this->notify('info', 'Spam risk detection dispatched!');
                    }),
                \Filament\Actions\Action::make('optimize_authority')
                    ->label('Optimize Authority')
                    ->icon('heroicon-o-academic-cap')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function () {
                        \App\Jobs\OptimizeAuthorityJob::dispatch();
                        $this->notify('info', 'Authority optimization dispatched!');
                    }),
            ])->label('⚡ QUALITY OPERATIONS')
                ->icon('heroicon-o-cog-6-tooth')
                ->color('gray')
                ->button(),
        ];
    }
}
