<?php

namespace App\Filament\Widgets\Quality;

use App\Models\ContentDepthScore;
use Filament\Widgets\ChartWidget;

class ContentDepthWidget extends ChartWidget
{
    protected static ?string $heading = 'Content Depth Distribution';
    protected int|string|array $columnSpan = 1;

    protected function getType(): string
    {
        return 'polarArea';
    }

    protected function getData(): array
    {
        $deep = ContentDepthScore::where('depth_score', '>=', 70)->count();
        $moderate = ContentDepthScore::whereBetween('depth_score', [40, 69])->count();
        $shallow = ContentDepthScore::where('depth_score', '<', 40)->count();
        $unscored = 0;

        return [
            'datasets' => [
                [
                    'data' => [$deep, $moderate, $shallow],
                    'backgroundColor' => ['#22c55e', '#eab308', '#ef4444'],
                ],
            ],
            'labels' => ['Deep Content (70+)', 'Moderate (40-69)', 'Shallow (<40)'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
