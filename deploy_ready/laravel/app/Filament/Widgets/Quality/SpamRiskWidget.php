<?php

namespace App\Filament\Widgets\Quality;

use App\Models\AntiSpamRiskScore;
use App\Models\ContentNode;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class SpamRiskWidget extends BaseWidget
{
    protected static ?string $heading = 'Top Spam Risk Content';
    protected int|string|array $columnSpan = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ContentNode::whereHas('spamRisk', function ($q) {
                    $q->where('overall_spam_risk_score', '>=', 50);
                })
                ->with('spamRisk')
                ->orderBy(
                    AntiSpamRiskScore::select('overall_spam_risk_score')
                        ->whereColumn('content_node_id', 'content_nodes.id')
                        ->limit(1),
                    'desc'
                )
                ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('seo_title')
                    ->label('Title')
                    ->limit(40)
                    ->searchable(),
                Tables\Columns\TextColumn::make('spamRisk.overall_spam_risk_score')
                    ->label('Spam Risk')
                    ->badge()
                    ->color(fn ($state) => match(true) {
                        $state >= 70 => 'danger',
                        $state >= 50 => 'warning',
                        default => 'success',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('spamRisk.template_overuse_score')
                    ->label('Template')
                    ->sortable(),
                Tables\Columns\TextColumn::make('spamRisk.doorway_page_risk_score')
                    ->label('Doorway')
                    ->sortable(),
                Tables\Columns\TextColumn::make('spamRisk.thin_content_risk_score')
                    ->label('Thin')
                    ->sortable(),
            ]);
    }
}
