<?php

namespace App\Filament\Resources\GlobalAdBlockResource\Pages;

use App\Filament\Resources\GlobalAdBlockResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGlobalAdBlocks extends ListRecords
{
    protected static string $resource = GlobalAdBlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
?>