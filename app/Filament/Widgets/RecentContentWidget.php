<?php

namespace App\Filament\Widgets;

use App\Models\ContentNode;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentContentWidget extends TableWidget
{
    protected static ?string $heading = '🚀 Recent Content Nodes';
    protected static ?int $sort = 3;
    protected int $defaultPaginationPageSize = 5;

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('id')
                ->label('ID')
                ->width(50),
            
            TextColumn::make('seo_title')
                ->label('Title')
                ->searchable()
                ->limit(35)
                ->wrap(),
            
            BadgeColumn::make('is_restricted_content')
                ->label('Status')
                ->formatStateUsing(fn (bool $state): string => $state ? '🔴 RESTRICTED' : '🟢 SAFE')
                ->colors([
                    'danger' => true,
                    'success' => false,
                ]),

            TextColumn::make('page_views')
                ->label('Hits')
                ->formatStateUsing(fn ($state) => number_format($state))
                ->badge()
                ->color(fn ($state) => $state > 1000 ? 'success' : ($state > 100 ? 'warning' : 'gray')),

            TextColumn::make('created_at')
                ->label('Created')
                ->dateTime('d M H:i')
                ->sortable(),
        ];
    }

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return ContentNode::query()
            ->latest()
            ->limit(5);
    }

    protected function getTableActions(): array
    {
        return [];
    }
}