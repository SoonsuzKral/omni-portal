<?php

namespace App\Filament\Resources;

use App\Models\Taxonomy;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;

class TaxonomyResource extends Resource
{
    protected static ?string $model = Taxonomy::class;
    protected static ?string $navigationIcon = 'heroicon-o-folder-arrow-down';
    protected static ?string $navigationLabel = 'Taxonomies';
    protected static ?string $navigationGroup = '📊 THE DATA MATRIX';
    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->label('Category Name'),
            Forms\Components\TextInput::make('slug')
                ->required()
                ->unique(table: static::$model, column: 'slug', ignoreRecord: true),
            Forms\Components\Select::make('parent_id')
                ->label('Parent Category')
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
                    ->width(60),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(['name', 'slug'])
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(['slug'])
                    ->color('gray')
                    ->limit(25),
                BadgeColumn::make('parent_id')
                    ->label('Hierarchy')
                    ->formatStateUsing(fn ($state) => $state ? '📂 Sub-category' : '🌐 Root Category')
                    ->colors([
                        'info' => fn ($state) => !$state,
                        'warning' => fn ($state) => $state,
                    ]),
                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Parent Category')
                    ->toggleable()
                    ->limit(20),
                Tables\Columns\TextColumn::make('children_count')
                    ->label('Sub-cats')
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
                Tables\Filters\Filter::make('root_taxonomies')
                    ->label('🌐 Root Categories Only')
                    ->query(fn ($query) => $query->whereNull('parent_id')),
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
            'index' => \App\Filament\Resources\TaxonomyResource\Pages\ListTaxonomies::route('/'),
            'create' => \App\Filament\Resources\TaxonomyResource\Pages\CreateTaxonomy::route('/create'),
            'edit' => \App\Filament\Resources\TaxonomyResource\Pages\EditTaxonomy::route('/{record}/edit'),
        ];
    }
}