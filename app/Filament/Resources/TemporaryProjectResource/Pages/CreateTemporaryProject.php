<?php

namespace App\Filament\Resources\TemporaryProjectResource\Pages;

use App\Filament\Resources\TemporaryProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTemporaryProject extends CreateRecord
{
    protected static string $resource = TemporaryProjectResource::class;
}
