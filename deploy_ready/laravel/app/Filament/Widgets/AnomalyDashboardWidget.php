<?php

namespace App\Filament\Widgets;

use App\Models\AnomalyDetection;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class AnomalyDashboardWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Active Anomalies';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AnomalyDetection::active()
                    ->with('contentNode:id,slug,seo_title')
                    ->orderBy('detected_at', 'desc')
                    ->limit(50)
            )
            ->columns([
                TextColumn::make('anomaly_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sudden_deindexing' => 'danger',
                        'ctr_collapse' => 'warning',
                        'ranking_volatility' => 'warning',
                        'crawl_drop' => 'danger',
                        'sitemap_fetch_failure' => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),

                TextColumn::make('severity')
                    ->label('Severity')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'critical' => 'danger',
                        'warning' => 'warning',
                        'info' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('description')
                    ->label('Description')
                    ->words(15)
                    ->searchable(),

                TextColumn::make('current_value')
                    ->label('Current')
                    ->numeric(2),

                TextColumn::make('previous_value')
                    ->label('Previous')
                    ->numeric(2),

                TextColumn::make('deviation')
                    ->label('Deviation')
                    ->numeric(2)
                    ->color(fn ($record): string =>
                        $record && $record->deviation > 50 ? 'danger' : 'gray'
                    ),

                TextColumn::make('detected_at')
                    ->label('Detected')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                TextColumn::make('contentNode.seo_title')
                    ->label('Content')
                    ->limit(30)
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\Action::make('resolve')
                    ->label('Resolve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (AnomalyDetection $record) {
                        $record->update([
                            'is_active' => false,
                            'resolved_at' => now(),
                        ]);
                    }),
            ])
            ->poll('60s')
            ->defaultSort('detected_at', 'desc');
    }
}
