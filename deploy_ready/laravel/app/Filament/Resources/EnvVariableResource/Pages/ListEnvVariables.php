<?php

namespace App\Filament\Resources\EnvVariableResource\Pages;

use App\Filament\Resources\EnvVariableResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEnvVariables extends ListRecords
{
    protected static string $resource = EnvVariableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
