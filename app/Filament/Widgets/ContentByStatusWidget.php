<?php

namespace App\Filament\Widgets;

use App\Models\ContentNode;
use Filament\Widgets\ChartWidget;

class ContentByStatusWidget extends ChartWidget
{
    protected static ?string $heading = '🎯 Content Distribution Matrix';
    protected static ?string $maxHeight = '200px';

    protected function getData(): array
    {
        $published = ContentNode::whereNotNull('publish_date')->count();
        $draft = ContentNode::whereNull('publish_date')->count();
        $restricted = ContentNode::where('is_restricted_content', true)->count();

        return [
            'datasets' => [
                [
                    'label' => 'Content Nodes',
                    'data' => [$published, $draft, $restricted],
                    'backgroundColor' => [
                        '#10b981',
                        '#6366f1',
                        '#ef4444',
                    ],
                    'borderWidth' => 0,
                ],
            ],
            'labels' => ['✅ Published', '📝 Draft', '🔴 Restricted'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'right',
                    'labels' => [
                        'color' => '#9ca3af',
                        'padding' => 15,
                        'usePointStyle' => true,
                        'pointStyle' => 'circle',
                    ],
                ],
            ],
            'maintainAspectRatio' => false,
            'cutout' => '65%',
        ];
    }
}