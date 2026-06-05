<?php

namespace App\Filament\Resources\EnvVariableResource\Pages;

use App\Filament\Resources\EnvVariableResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEnvVariable extends EditRecord
{
    protected static string $resource = EnvVariableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
