<?php

namespace App\Filament\Resources\ContentNodeResource\Pages;

use App\Filament\Resources\ContentNodeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListContentNodes extends ListRecords
{
    protected static string $resource = ContentNodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
?>