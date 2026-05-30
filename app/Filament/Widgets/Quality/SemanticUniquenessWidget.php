<?php

namespace App\Filament\Widgets\Quality;

use App\Models\SemanticUniquenessScore;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class SemanticUniquenessWidget extends ChartWidget
{
    protected static ?string $heading = 'Semantic Uniqueness Distribution';
    protected int|string|array $columnSpan = 2;

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $high = SemanticUniquenessScore::where('overall_uniqueness_score', '>=', 70)->count();
        $medium = SemanticUniquenessScore::whereBetween('overall_uniqueness_score', [40, 69])->count();
        $low = SemanticUniquenessScore::where('overall_uniqueness_score', '<', 40)->count();

        return [
            'datasets' => [
                [
                    'data' => [$high, $medium, $low],
                    'backgroundColor' => ['#22c55e', '#eab308', '#ef4444'],
                    'hoverOffset' => 8,
                ],
            ],
            'labels' => ['High Uniqueness (70-100)', 'Medium (40-69)', 'Low Uniqueness (0-39)'],
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
