<?php

namespace App\Filament\Widgets\Quality;

use App\Models\UserSatisfactionScore;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class EngagementQualityWidget extends ChartWidget
{
    protected static ?string $heading = 'Engagement Quality Metrics';
    protected int|string|array $columnSpan = 1;

    protected function getType(): string
    {
        return 'radar';
    }

    protected function getData(): array
    {
        $avgDwell = UserSatisfactionScore::avg('dwell_time_score');
        $avgScroll = UserSatisfactionScore::avg('scroll_depth_score');
        $avgInteraction = UserSatisfactionScore::avg('interaction_rate_score');
        $avgBounce = UserSatisfactionScore::avg('bounce_behavior_score');
        $avgCta = UserSatisfactionScore::avg('cta_engagement_score');
        $avgNav = UserSatisfactionScore::avg('navigation_depth_score');

        return [
            'datasets' => [
                [
                    'label' => 'Average Scores',
                    'data' => [
                        round($avgDwell ?? 0, 1),
                        round($avgScroll ?? 0, 1),
                        round($avgInteraction ?? 0, 1),
                        round($avgBounce ?? 0, 1),
                        round($avgCta ?? 0, 1),
                        round($avgNav ?? 0, 1),
                    ],
                    'backgroundColor' => 'rgba(6, 182, 212, 0.2)',
                    'borderColor' => '#06b6d4',
                    'pointBackgroundColor' => '#22d3ee',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => [
                'Dwell Time',
                'Scroll Depth',
                'Interaction Rate',
                'Bounce Behavior',
                'CTA Engagement',
                'Navigation Depth',
            ],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'r' => [
                    'beginAtZero' => true,
                    'max' => 100,
                ],
            ],
        ];
    }
}
