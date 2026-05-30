<?php

namespace App\Filament\Resources;

use App\Models\GlobalAdBlock;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Columns\ToggleColumn;

class GlobalAdBlockResource extends Resource
{
    protected static ?string $model = GlobalAdBlock::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Ad Blocks';
    protected static ?string $navigationGroup = '🚀 ENGINE ROOM';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->label('Block Name')
                ->columnSpan(2),
            Forms\Components\Select::make('position')
                ->options(\App\Models\GlobalAdBlock::POSITIONS)
                ->required()
                ->searchable()
                ->label('Ad Slot Position')
                ->helperText('Head Script = Google Auto Ads kodu. GA4/Clarity = Tracking kodlarınızı eksiksiz yapıştırın (<head> içine eklenir)'),
            Forms\Components\Select::make('network_type')
                ->options([
                    'Safe' => '🛡️ Safe Network',
                    'Restricted' => '🔞 Restricted Content',
                    'Custom' => '⚙️ Custom Script',
                ])
                ->required()
                ->label('Ad Network'),
            Forms\Components\Textarea::make('script')
                ->rows(5)
                ->label('Ad Script / Code')
                ->helperText('Paste your ad script here (HTML/JS)')
                ->columnSpan(2),
            Forms\Components\KeyValue::make('forbidden_locations')
                ->keyLabel('Location IDs')
                ->valueLabel('Location Names')
                ->label('🚫 Forbidden Locations')
                ->helperText('Add locations where this ad should NOT appear')
                ->addButtonLabel('Add Forbidden Location')
                ->columnSpan(2),
            Forms\Components\TextInput::make('cpm_note')
                ->maxLength(255)
                ->label('💰 CPM/CPC Note')
                ->placeholder('e.g., $5.00 CPM, CPC: $0.25')
                ->helperText('Revenue tracking note for this ad block')
                ->columnSpan(2),
            Forms\Components\Toggle::make('active')
                ->default(true)
                ->label('Active'),
            Forms\Components\Toggle::make('is_global')
                ->default(true)
                ->label('🌐 Show in All Categories')
                ->helperText('If OFF, select a specific category below')
                ->columnSpan(2),
            Forms\Components\Select::make('taxonomy_id')
                ->label('📂 Specific Category')
                ->helperText('Select a category to target this ad specifically')
                ->options(\App\Models\Taxonomy::pluck('name', 'id'))
                ->searchable()
                ->nullable()
                ->hidden(fn (Forms\Get $get) => $get('is_global')),
        ]);
    }

public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->select(['id', 'position', 'name', 'network_type', 'active', 'created_at', 'updated_at']))
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->width(60),
                Tables\Columns\TextColumn::make('position')
                    ->searchable(['position'])
                    ->label('Position')
                    ->badge()
                    ,
                Tables\Columns\TextColumn::make('name')
                    ->searchable(['name'])
                    ->label('Name')
                    ->weight('medium')
                    ,
                Tables\Columns\TextColumn::make('network_type')
                    ->label('Network')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Safe' => 'success',
                        'Restricted' => 'danger',
                        'Custom' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\ToggleColumn::make('active')
                    ->label('Active')
                    ->onColor('success')
                    ->offColor('gray'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->label('Created')
                    ->toggleable()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('active_only')
                    ->label('✅ Active Only')
                    ->query(fn ($query) => $query->where('active', true)),
                Tables\Filters\Filter::make('inactive_only')
                    ->label('❌ Inactive Only')
                    ->query(fn ($query) => $query->where('active', false)),
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
                    Tables\Actions\BulkAction::make('activate')
                        ->label('✅ Activate All')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn ($records) => $records->each->update(['active' => true])),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('❌ Deactivate All')
                        ->icon('heroicon-o-x-circle')
                        ->action(fn ($records) => $records->each->update(['active' => false])),
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
            'index' => \App\Filament\Resources\GlobalAdBlockResource\Pages\ListGlobalAdBlocks::route('/'),
            'create' => \App\Filament\Resources\GlobalAdBlockResource\Pages\CreateGlobalAdBlock::route('/create'),
            'edit' => \App\Filament\Resources\GlobalAdBlockResource\Pages\EditGlobalAdBlock::route('/{record}/edit'),
        ];
    }
}