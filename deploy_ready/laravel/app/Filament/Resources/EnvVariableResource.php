<?php

namespace App\Filament\Resources;

use App\Models\EnvVariable;
use App\Services\EnvManager;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Artisan;

class EnvVariableResource extends Resource
{
    protected static ?string $model = EnvVariable::class;
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Env Manager';
    protected static ?string $navigationGroup = '⚙️ SYSTEM';
    protected static ?int $navigationSort = 100;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('key')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255)
                ->label('Environment Key')
                ->disabled(fn ($record) => $record?->is_system ?? false),
            Forms\Components\TextInput::make('value')
                ->label('Value')
                ->nullable()
                ->type(fn ($record) => $record?->is_encrypted ? 'password' : 'text')
                ->revealable(fn ($record) => $record?->is_encrypted ?? false),
            Forms\Components\Select::make('category')
                ->options(EnvVariable::categories())
                ->label('Category')
                ->default('general'),
            Forms\Components\Textarea::make('description')
                ->label('Description')
                ->rows(2)
                ->nullable(),
            Forms\Components\Toggle::make('is_encrypted')
                ->label('Encrypt Value')
                ->default(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->searchable()
                    ->label('Key')
                    ->fontFamily('mono')
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('value')
                    ->label('Value')
                    ->fontFamily('mono')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->value)
                    ->formatStateUsing(fn ($state) => $state ? '••••••••' : '-')
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('category')
                    ->label('Category')
                    ->colors([
                        'primary' => 'general',
                        'success' => 'database',
                        'warning' => 'cache',
                        'info' => 'queue',
                        'danger' => 'mail',
                        'gray' => 'storage',
                        'purple' => 'elasticsearch',
                        'orange' => 'cloudflare',
                    ]),
                Tables\Columns\IconColumn::make('is_service_enabled')
                    ->label('Service')
                    ->boolean()
                    ->icon(fn ($state) => $state ? 'heroicon-o-check-circle' : null)
                    ->tooltip(fn ($record) => $record->service_name ? 'Part of: ' . $record->service_name : null),
                Tables\Columns\IconColumn::make('is_system')
                    ->label('System')
                    ->boolean()
                    ->icon(fn ($state) => $state ? 'heroicon-o-lock-closed' : null)
                    ->tooltip('System variable - cannot be deleted'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('d M Y H:i')
                    ->toggleable()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Category')
                    ->options(EnvVariable::categories()),
                Tables\Filters\Filter::make('service_variables')
                    ->label('Service Variables')
                    ->query(fn ($query) => $query->whereNotNull('service_name')),
                Tables\Filters\Filter::make('enabled_services')
                    ->label('Enabled Services')
                    ->query(fn ($query) => $query->where('is_service_enabled', true)),
                Tables\Filters\Filter::make('system_only')
                    ->label('System Keys')
                    ->query(fn ($query) => $query->where('is_system', true)),
                Tables\Filters\Filter::make('custom_only')
                    ->label('Custom Keys')
                    ->query(fn ($query) => $query->where('is_system', false)),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil-square'),
                Tables\Actions\Action::make('view_value')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->modalContent(fn ($record) => view('filament.modals.env-value', ['record' => $record])),
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn ($record) => $record->is_system)
                    ->icon('heroicon-o-trash'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('sync_from_env')
                    ->label('📥 Sync from .env')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () {
                        $envManager = app(EnvManager::class);
                        $count = $envManager->loadFromEnvFile();
                        Notification::make()
                            ->title('Sync Complete')
                            ->body("Synced {$count} variables from .env file")
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('export_to_env')
                    ->label('📤 Export to .env')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->action(function () {
                        $envManager = app(EnvManager::class);
                        $envManager->exportToEnvFile();
                        Artisan::call('config:clear');
                        Notification::make()
                            ->title('Export Complete')
                            ->body('Variables exported to .env file')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('services')
                    ->label('🛠️ Services')
                    ->icon('heroicon-o-puzzle-piece')
                    ->url('/admin/env-variables/services'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('delete_custom')
                        ->label('Delete Selected (Custom Only)')
                        ->action(fn ($records) => $records->where('is_system', false)->delete()),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\EnvVariableResource\Pages\ListEnvVariables::route('/'),
            'create' => \App\Filament\Resources\EnvVariableResource\Pages\CreateEnvVariable::route('/create'),
            'edit' => \App\Filament\Resources\EnvVariableResource\Pages\EditEnvVariable::route('/{record}/edit'),
        ];
    }
}