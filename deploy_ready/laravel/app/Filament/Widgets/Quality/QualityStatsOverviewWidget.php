<?php

namespace App\Filament\Widgets\Quality;

use App\Models\ContentNode;
use App\Models\AntiSpamRiskScore;
use App\Models\TopicAuthorityScore;
use App\Models\EeatSignal;
use App\Models\EntityAuthorityGraph;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class QualityStatsOverviewWidget extends BaseWidget
{
    protected int $columns = 5;
    protected ?string $loadingIndicator = 'Loading quality metrics...';

    protected function getStats(): array
    {
        $totalScored = ContentNode::whereNotNull('last_quality_analyzed_at')->count();
        $totalContent = ContentNode::whereNotNull('body_content')->count();
        $coveragePercent = $totalContent > 0 ? round(($totalScored / $totalContent) * 100, 1) : 0;

        $avgEeat = ContentNode::whereNotNull('eeat_score')->avg('eeat_score');
        $avgHumanization = ContentNode::whereNotNull('humanization_score')->avg('humanization_score');
        $avgUniqueness = DB::table('semantic_uniqueness_scores')->avg('overall_uniqueness_score');
        $avgSpamRisk = AntiSpamRiskScore::avg('overall_spam_risk_score');
        $entityCount = EntityAuthorityGraph::count();

        return [
            Stat::make('🎯 Quality Coverage', number_format($totalScored) . ' / ' . number_format($totalContent))
                ->description($coveragePercent . '% of content analyzed')
                ->icon('heroicon-o-check-badge')
                ->color($coveragePercent > 80 ? 'success' : ($coveragePercent > 50 ? 'warning' : 'danger')),

            Stat::make('📊 Avg EEAT Score', $avgEeat ? number_format($avgEeat, 1) . '%' : 'N/A')
                ->description('Experience, Expertise, Authoritativeness')
                ->icon('heroicon-o-academic-cap')
                ->color($avgEeat && $avgEeat > 70 ? 'success' : ($avgEeat && $avgEeat > 40 ? 'warning' : 'danger')),

            Stat::make('🧠 Avg Uniqueness', $avgUniqueness ? number_format($avgUniqueness, 1) . '%' : 'N/A')
                ->description('Semantic uniqueness score')
                ->icon('heroicon-o-variable')
                ->color($avgUniqueness && $avgUniqueness > 70 ? 'success' : 'warning'),

            Stat::make('👤 Avg Humanization', $avgHumanization ? number_format($avgHumanization, 1) . '%' : 'N/A')
                ->description('AI detection resistance')
                ->icon('heroicon-o-user')
                ->color($avgHumanization && $avgHumanization > 60 ? 'success' : 'warning'),

            Stat::make('⚔️ Entities in Graph', number_format($entityCount))
                ->description('Across ' . EntityAuthorityGraph::distinct('entity_type')->count() . ' types')
                ->icon('heroicon-o-share-nodes')
                ->color('info'),
        ];
    }
}
