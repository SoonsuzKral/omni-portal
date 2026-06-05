<?php

namespace App\Filament\Resources;

use App\Models\Location;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;

class LocationResource extends Resource
{
    protected static ?string $model = Location::class;
    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationLabel = 'Locations';
    protected static ?string $navigationGroup = '📊 THE DATA MATRIX';
    protected static ?int $navigationSort = 30;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\TextInput::make('slug')->required()->unique(table: static::$model, column: 'slug', ignoreRecord: true),
            Forms\Components\Select::make('parent_id')
                ->label('Parent Location')
                ->relationship('parent', 'name')
                ->searchable()
                ->placeholder('No parent (root level)'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query
                ->withCount('children')
                ->select(['id', 'name', 'slug', 'parent_id'])
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->label('ID')
                    ->width(60),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(['name', 'slug'])
                    ->label('Name')
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(['slug'])
                    ->label('Slug')
                    ->color('gray')
                    ->limit(25),
                BadgeColumn::make('parent_id')
                    ->label('Type')
                    ->formatStateUsing(fn ($state) => $state ? '📍 District' : '🌐 City')
                    ->colors([
                        'info' => fn ($state) => !$state,
                        'warning' => fn ($state) => $state,
                    ]),
                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Parent City')
                    ->toggleable()
                    ->limit(20),
                Tables\Columns\TextColumn::make('children_count')
                    ->label('Districts')
                    ->badge()
                    ->color('primary'),
                BadgeColumn::make('locale')
                    ->label('Locale')
                    ->colors([
                        'success' => 'TR',
                        'info'    => 'EN',
                        'warning' => 'AR',
                        'danger'  => 'RU',
                    ])
                    ->sortable()
                    ->alignCenter(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('locale')
                    ->label('Locale')
                    ->options([
                        'TR' => '🇹🇷 Turkish',
                        'EN' => '🇺🇸 English',
                        'AR' => '🇦🇪 Arabic',
                        'RU' => '🇷🇺 Russian',
                    ]),
                Tables\Filters\Filter::make('root_locations')
                    ->label('🌐 Cities Only')
                    ->query(fn ($query) => $query->whereNull('parent_id')),
                Tables\Filters\Filter::make('has_districts')
                    ->label('📍 With Districts')
                    ->query(fn ($query) => $query->has('children')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil-square'),
                Tables\Actions\DeleteAction::make()
                    ->icon('heroicon-o-trash'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->paginationPageOptions([25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->persistSortInSession()
            ->persistFiltersInSession();
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\LocationResource\Pages\ListLocations::route('/'),
            'create' => \App\Filament\Resources\LocationResource\Pages\CreateLocation::route('/create'),
            'edit' => \App\Filament\Resources\LocationResource\Pages\EditLocation::route('/{record}/edit'),
        ];
    }
}