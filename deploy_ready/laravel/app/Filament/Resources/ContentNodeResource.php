<?php

namespace App\Filament\Resources;

use App\Models\ContentNode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Support\Enums\Alignment;

class ContentNodeResource extends Resource
{
    protected static ?string $model = ContentNode::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Content Nodes';
    protected static ?string $navigationGroup = '📊 THE DATA MATRIX';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('uuid')->required()->unique()->maxLength(36),
            Forms\Components\Select::make('post_template_id')
                ->label('Post Template')
                ->relationship('postTemplate', 'name')
                ->searchable()
                ->nullable(),
            Forms\Components\Select::make('taxonomy_id')
                ->label('Taxonomy')
                ->relationship('taxonomy', 'name')
                ->searchable()
                ->required(),
            Forms\Components\Select::make('location_id')
                ->label('Location')
                ->relationship('location', 'name')
                ->searchable()
                ->nullable(),
            Forms\Components\TextInput::make('seo_title')->required()->maxLength(255),
            Forms\Components\TextInput::make('slug')->required()->unique(table: static::$model, column: 'slug', ignoreRecord: true),
            Forms\Components\Textarea::make('body_content')->required(),
            Forms\Components\Toggle::make('is_restricted_content')->label('Restricted Content')->default(false),
            Forms\Components\Toggle::make('ads_enabled')->label('Enable Ads')->default(true),
            Forms\Components\TextInput::make('page_views')->numeric()->default(0),
            Forms\Components\TextInput::make('publish_date')->nullable()->label('Publish Date (Y-m-d H:i:s)'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['location', 'taxonomy']))
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable()->label('ID')->width(60),
                Tables\Columns\TextColumn::make('seo_title')
                    ->searchable(['seo_title', 'slug'])
                    ->limit(40)
                    ->wrap(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(['slug'])
                    ->limit(25)
                    ->toggleable()
                    ->color('gray'),
                BadgeColumn::make('is_restricted_content')
                    ->label('Status')
                    ->colors([
                        'danger' => true,
                        'success' => false,
                    ])
                    ->formatStateUsing(fn (bool $state): string => $state ? '🔴 RESTRICTED' : '🟢 SAFE')
                    ->sortable()
                    ->alignCenter(),
                ToggleColumn::make('ads_enabled')
                    ->label('Ads')
                    ->onColor('success')
                    ->offColor('gray')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('page_views')
                    ->sortable()
                    ->label('Hits')
                    ->formatStateUsing(fn ($state) => number_format($state))
                    ->badge()
                    ->color(fn ($state) => $state > 1000 ? 'success' : ($state > 100 ? 'warning' : 'gray'))
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('location.name')
                    ->label('Location')
                    ->toggleable()
                    ->limit(20),
                Tables\Columns\TextColumn::make('taxonomy.name')
                    ->label('Category')
                    ->toggleable()
                    ->limit(20),
                Tables\Columns\TextColumn::make('crawl_priority_score')
                    ->sortable()
                    ->label('Priority')
                    ->formatStateUsing(fn ($state) => number_format($state ?? 0, 1))
                    ->badge()
                    ->color(fn ($state) => $state >= 70 ? 'success' : ($state >= 40 ? 'warning' : 'gray'))
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('publish_date')
                    ->sortable()
                    ->dateTime('d M Y'),
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
                Tables\Filters\SelectFilter::make('is_restricted_content')
                    ->label('Content Type')
                    ->options([
                        false => '🟢 Safe Content',
                        true => '🔴 Restricted Content',
                    ]),
                Tables\Filters\Filter::make('most_popular')
                    ->label('🔥 Hot Content (1K+ hits)')
                    ->query(fn ($query) => $query->where('page_views', '>=', 1000)),
                Tables\Filters\Filter::make('only_restricted')
                    ->label('🔴 Only Restricted')
                    ->query(fn ($query) => $query->where('is_restricted_content', true)),
                Tables\Filters\Filter::make('published_only')
                    ->label('✅ Only Published')
                    ->query(fn ($query) => $query->whereNotNull('publish_date')),
                Tables\Filters\Filter::make('ads_enabled')
                    ->label('📺 Ads Enabled')
                    ->query(fn ($query) => $query->where('ads_enabled', true)),
                Tables\Filters\SelectFilter::make('crawl_priority_score')
                    ->label('Crawl Priority')
                    ->options([
                        'high' => '🔥 High (70+)',
                        'medium' => '📊 Medium (40-69)',
                        'low' => '⬇ Low (< 40)',
                        'unscored' => '❌ Unscored',
                    ])
                    ->query(fn ($query, $state) => match ($state) {
                        'high' => $query->highPriority(),
                        'medium' => $query->priorityRange(40, 69.99),
                        'low' => $query->priorityRange(0, 39.99),
                        'unscored' => $query->unscored(),
                        default => $query,
                    }),
            ])
            ->defaultSort('crawl_priority_score', 'desc')
            ->actions([
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil-square'),
                Tables\Actions\DeleteAction::make()
                    ->icon('heroicon-o-trash'),
                Tables\Actions\Action::make('view_page')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => "/{$record->slug}")
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('enable_ads')
                        ->label('📺 Enable Ads')
                        ->icon('heroicon-o-speaker-wave')
                        ->action(fn ($records) => $records->each->update(['ads_enabled' => true])),
                    Tables\Actions\BulkAction::make('disable_ads')
                        ->label('🔇 Disable Ads')
                        ->icon('heroicon-o-speaker-x-mark')
                        ->action(fn ($records) => $records->each->update(['ads_enabled' => false])),
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
            'index' => \App\Filament\Resources\ContentNodeResource\Pages\ListContentNodes::route('/'),
            'create' => \App\Filament\Resources\ContentNodeResource\Pages\CreateContentNode::route('/create'),
            'edit' => \App\Filament\Resources\ContentNodeResource\Pages\EditContentNode::route('/{record}/edit'),
        ];
    }
}