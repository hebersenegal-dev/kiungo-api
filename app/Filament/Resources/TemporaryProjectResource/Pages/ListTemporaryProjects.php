<?php

namespace App\Filament\Resources\TemporaryProjectResource\Pages;

use App\Filament\Resources\TemporaryProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTemporaryProjects extends ListRecords
{
    protected static string $resource = TemporaryProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
        ];
    }
}
