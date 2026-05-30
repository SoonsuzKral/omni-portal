<?php

namespace App\Filament\Resources;

use App\Models\LiveDataVault;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;

class LiveDataVaultResource extends Resource
{
    protected static ?string $model = LiveDataVault::class;
    protected static ?string $navigationIcon = 'heroicon-o-server-stack';
    protected static ?string $navigationLabel = 'Live Data Vaults';
    protected static ?string $navigationGroup = '🚀 ENGINE ROOM';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('key')
                ->required()
                ->maxLength(255)
                ->label('Key'),
            Forms\Components\Textarea::make('value')
                ->required()
                ->label('Value'),
            Forms\Components\Select::make('data_type')
                ->options([
                    'string' => 'String',
                    'json' => 'JSON',
                    'array' => 'Array',
                    'integer' => 'Integer',
                    'boolean' => 'Boolean',
                ])
                ->default('string')
                ->label('Data Type'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->select(['id', 'key', 'value', 'data_type', 'created_at', 'updated_at']))
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->width(60),
                Tables\Columns\TextColumn::make('key')
                    ->searchable(['key'])
                    ->weight('medium')
                    ->limit(40),
                Tables\Columns\TextColumn::make('value')
                    ->limit(60)
                    ->toggleable(),
                BadgeColumn::make('data_type')
                    ->label('Type')
                    ->colors([
                        'info' => 'string',
                        'warning' => 'json',
                        'success' => 'array',
                        'primary' => 'integer',
                        'gray' => 'boolean',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y H:i')
                    ->label('Created')
                    ->toggleable()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('data_type')
                    ->label('Data Type')
                    ->options([
                        'string' => 'String',
                        'json' => 'JSON',
                        'array' => 'Array',
                        'integer' => 'Integer',
                        'boolean' => 'Boolean',
                    ]),
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
            'index' => \App\Filament\Resources\LiveDataVaultResource\Pages\ListLiveDataVaults::route('/'),
            'create' => \App\Filament\Resources\LiveDataVaultResource\Pages\CreateLiveDataVault::route('/create'),
            'edit' => \App\Filament\Resources\LiveDataVaultResource\Pages\EditLiveDataVault::route('/{record}/edit'),
        ];
    }
}