<?php

namespace App\Filament\Resources;

use App\Models\Keyword;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;

class KeywordResource extends Resource
{
    protected static ?string $model = Keyword::class;
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $navigationLabel = 'Keywords';
    protected static ?string $navigationGroup = '📊 THE DATA MATRIX';
    protected static ?int $navigationSort = 40;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('keyword')
                ->required()
                ->maxLength(255)
                ->label('Keyword'),
            Forms\Components\TextInput::make('slug')
                ->required()
                ->unique(table: Keyword::class, column: 'slug', ignoreRecord: true),
            Forms\Components\Select::make('language')
                ->options([
                    'tr' => '🇹🇷 Turkish',
                    'en' => '🇬🇧 English',
                    'ar' => '🇸🇦 Arabic',
                    'ru' => '🇷🇺 Russian',
                    'fa' => '🇮🇷 Persian',
                    'fr' => '🇫🇷 French',
                ])
                ->default('tr')
                ->label('Language'),
            Forms\Components\Select::make('category_id')
                ->label('Category')
                ->relationship('category', 'name')
                ->searchable()
                ->placeholder('No category'),
            Forms\Components\Select::make('location_id')
                ->label('Location')
                ->relationship('location', 'name')
                ->searchable()
                ->placeholder('No location'),
            Forms\Components\TextInput::make('search_volume')
                ->numeric()
                ->minValue(0)
                ->label('Search Volume'),
            Forms\Components\TextInput::make('difficulty')
                ->numeric()
                ->minValue(0)
                ->maxValue(100)
                ->label('Difficulty (0-100)'),
            Forms\Components\TextInput::make('clicks')
                ->numeric()
                ->minValue(0)
                ->label('Clicks'),
            Forms\Components\TextInput::make('impressions')
                ->numeric()
                ->minValue(0)
                ->label('Impressions'),
            Forms\Components\TextInput::make('position')
                ->numeric()
                ->minValue(0)
                ->label('Position'),
            Forms\Components\Checkbox::make('is_trending')
                ->label('Trending 🔥'),
            Forms\Components\Checkbox::make('is_auto_generated')
                ->label('Auto Generated 🤖'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['category', 'location']))
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->width(60),
                Tables\Columns\TextColumn::make('keyword')
                    ->searchable(['keyword', 'slug'])
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(['slug'])
                    ->color('gray')
                    ->limit(25),
                BadgeColumn::make('language')
                    ->label('Lang')
                    ->colors([
                        'info' => 'tr',
                        'warning' => 'en',
                        'danger' => 'ar',
                        'success' => 'ru',
                        'primary' => 'fa',
                        'gray' => 'fr',
                    ]),
                Tables\Columns\TextColumn::make('search_volume')
                    ->sortable()
                    ->label('Volume')
                    ->formatStateUsing(fn ($state) => number_format($state ?? 0)),
                Tables\Columns\TextColumn::make('difficulty')
                    ->sortable()
                    ->label('Diff')
                    ->badge()
                    ->color(fn ($state) => $state > 70 ? 'danger' : ($state > 40 ? 'warning' : 'success')),
                ToggleColumn::make('is_trending')
                    ->label('🔥')
                    ->onColor('warning')
                    ->offColor('gray'),
                ToggleColumn::make('is_auto_generated')
                    ->label('🤖')
                    ->onColor('primary')
                    ->offColor('gray'),
                Tables\Columns\TextColumn::make('clicks')
                    ->sortable()
                    ->label('Clicks')
                    ->formatStateUsing(fn ($state) => number_format($state ?? 0)),
                Tables\Columns\TextColumn::make('position')
                    ->sortable()
                    ->label('Rank')
                    ->badge()
                    ->color(fn ($state) => $state <= 10 ? 'success' : ($state <= 30 ? 'warning' : 'danger')),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->toggleable()
                    ->limit(20),
                Tables\Columns\TextColumn::make('location.name')
                    ->label('Location')
                    ->toggleable()
                    ->limit(20),
            ])
            ->filters([
                SelectFilter::make('language')
                    ->multiple()
                    ->options([
                        'tr' => '🇹🇷 Turkish',
                        'en' => '🇬🇧 English',
                        'ar' => '🇸🇦 Arabic',
                        'ru' => '🇷🇺 Russian',
                        'fa' => '🇮🇷 Persian',
                        'fr' => '🇫🇷 French',
                    ]),
                SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Category'),
                SelectFilter::make('location_id')
                    ->relationship('location', 'name')
                    ->label('Location'),
                Filter::make('is_trending')
                    ->label('🔥 Trending Only')
                    ->query(fn ($query) => $query->where('is_trending', true)),
                Filter::make('is_auto_generated')
                    ->label('🤖 Auto Generated')
                    ->query(fn ($query) => $query->where('is_auto_generated', true)),
                Filter::make('top_keywords')
                    ->label('🏆 Top 10 Keywords')
                    ->query(fn ($query) => $query->where('position', '<=', 10)->orderBy('position')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil-square'),
                Tables\Actions\DeleteAction::make()
                    ->icon('heroicon-o-trash'),
                Tables\Actions\Action::make('create_content')
                    ->label('Create Content')
                    ->icon('heroicon-o-plus-circle')
                    ->url(fn (Keyword $record) => route('filament.admin.resources.content-nodes.create', ['keyword_id' => $record->id])),
            ])
            ->defaultSort('search_volume', 'desc')
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
            'index' => \App\Filament\Resources\KeywordResource\Pages\ListKeywords::route('/'),
            'create' => \App\Filament\Resources\KeywordResource\Pages\CreateKeyword::route('/create'),
            'edit' => \App\Filament\Resources\KeywordResource\Pages\EditKeyword::route('/{record}/edit'),
        ];
    }
}