<?php

namespace App\Filament\Widgets\Quality;

use App\Models\ContentNode;
use App\Models\EeatSignal;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class EeatMetricsWidget extends ChartWidget
{
    protected static ?string $heading = 'EEAT Signal Distribution';
    protected int|string|array $columnSpan = 2;

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $signalTypes = [
            'author_expertise' => 'Author Expertise',
            'editorial_review' => 'Editorial Review',
            'citation_quality' => 'Citation Quality',
            'source_trust' => 'Source Trust',
            'factual_confidence' => 'Factual Confidence',
            'content_freshness' => 'Content Freshness',
        ];

        $datasets = [];
        $labels = [];

        foreach ($signalTypes as $key => $label) {
            $avg = EeatSignal::where('signal_type', $key)->avg('signal_score');
            $datasets[] = round($avg ?? 0, 1);
            $labels[] = $label;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Average Score',
                    'data' => $datasets,
                    'backgroundColor' => [
                        '#06b6d4', '#22d3ee', '#0891b2',
                        '#0e7490', '#155e75', '#164e63',
                    ],
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'max' => 100,
                    'title' => [
                        'display' => true,
                        'text' => 'Score (%)',
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
        ];
    }
}
