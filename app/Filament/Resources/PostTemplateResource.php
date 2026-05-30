<?php

namespace App\Filament\Resources;

use App\Models\PostTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;

class PostTemplateResource extends Resource
{
    protected static ?string $model = PostTemplate::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Post Templates';
    protected static ?string $navigationGroup = '📊 THE DATA MATRIX';
    protected static ?int $navigationSort = 50;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->label('Template Name'),
            Forms\Components\TextInput::make('slug')
                ->required()
                ->unique(table: static::$model, column: 'slug', ignoreRecord: true),
            Forms\Components\Select::make('taxonomy_id')
                ->label('Taxonomy')
                ->relationship('taxonomy', 'name')
                ->searchable()
                ->nullable(),
            Forms\Components\Textarea::make('template_body')
                ->required()
                ->rows(10)
                ->label('Template Body'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->width(60),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->color('gray')
                    ->limit(25),
                Tables\Columns\TextColumn::make('taxonomy.name')
                    ->label('Category')
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->label('Created')
                    ->toggleable()
                    ->sortable(),
            ])
            ->filters([])
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
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\PostTemplateResource\Pages\ListPostTemplates::route('/'),
            'create' => \App\Filament\Resources\PostTemplateResource\Pages\CreatePostTemplate::route('/create'),
            'edit' => \App\Filament\Resources\PostTemplateResource\Pages\EditPostTemplate::route('/{record}/edit'),
        ];
    }
}