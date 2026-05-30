<?php

namespace App\Filament\Widgets\Quality;

use App\Models\TopicAuthorityScore;
use App\Models\Taxonomy;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class AuthorityClustersWidget extends BaseWidget
{
    protected static ?string $heading = 'Topical Authority Clusters';
    protected int|string|array $columnSpan = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TopicAuthorityScore::where('topicable_type', Taxonomy::class)
                    ->orderBy('authority_cluster_score', 'desc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('topicable_id')
                    ->label('Taxonomy')
                    ->formatStateUsing(function ($state) {
                        $tax = Taxonomy::find($state);
                        return $tax?->name ?? "Taxonomy #{$state}";
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('authority_cluster_score')
                    ->label('Authority')
                    ->badge()
                    ->color(fn ($state) => match(true) {
                        $state >= 70 => 'success',
                        $state >= 40 => 'warning',
                        default => 'danger',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('topic_coverage_score')
                    ->label('Coverage')
                    ->sortable(),
                Tables\Columns\TextColumn::make('entity_completeness_score')
                    ->label('Entities')
                    ->sortable(),
                Tables\Columns\TextColumn::make('internal_topical_links_score')
                    ->label('Links')
                    ->sortable(),
                Tables\Columns\TextColumn::make('cluster_members')
                    ->label('Nodes')
                    ->formatStateUsing(fn ($state) => is_array($state) ? count($state) : 0),
            ]);
    }
}
