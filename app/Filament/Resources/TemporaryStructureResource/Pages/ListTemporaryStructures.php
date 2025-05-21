<?php

namespace App\Filament\Resources\TemporaryStructureResource\Pages;

use App\Filament\Resources\TemporaryStructureResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTemporaryStructures extends ListRecords
{
    protected static string $resource = TemporaryStructureResource::class;

    protected function getHeaderActions(): array
    {
        return [
          //  Actions\CreateAction::make(),
        ];
    }
}
