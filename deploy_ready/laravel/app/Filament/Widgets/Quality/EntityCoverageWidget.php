<?php

namespace App\Filament\Widgets\Quality;

use App\Models\EntityAuthorityGraph;
use App\Models\ContentNode;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class EntityCoverageWidget extends BaseWidget
{
    protected static ?string $heading = 'Entity Graph Overview';
    protected int|string|array $columnSpan = 2;

    public function table(Table $table): Table
    {
        $entityTypes = EntityAuthorityGraph::select('entity_type', DB::raw('count(*) as total'), DB::raw('avg(entity_authority_score) as avg_authority'), DB::raw('avg(topical_relevance_score) as avg_relevance'), DB::raw('sum(mention_count) as total_mentions'))
            ->groupBy('entity_type')
            ->orderBy('total', 'desc')
            ->get();

        return $table
            ->query(
                EntityAuthorityGraph::query()
                    ->select('entity_type', DB::raw('count(*) as total'), DB::raw('avg(entity_authority_score) as avg_authority'), DB::raw('avg(topical_relevance_score) as avg_relevance'), DB::raw('sum(mention_count) as total_mentions'))
                    ->groupBy('entity_type')
                    ->orderBy('total', 'desc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('entity_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'city' => 'info',
                        'service' => 'success',
                        'tool' => 'warning',
                        'technology' => 'danger',
                        'company' => 'gray',
                        'industry' => 'primary',
                        'trend' => 'purple',
                        'person' => 'teal',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('total')
                    ->label('Count')
                    ->sortable(),
                Tables\Columns\TextColumn::make('avg_authority')
                    ->label('Avg Authority')
                    ->formatStateUsing(fn ($state) => number_format($state, 1) . '%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('avg_relevance')
                    ->label('Avg Relevance')
                    ->formatStateUsing(fn ($state) => number_format($state, 1) . '%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_mentions')
                    ->label('Total Mentions')
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
