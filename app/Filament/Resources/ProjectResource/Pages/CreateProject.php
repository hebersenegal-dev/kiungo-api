<?php

namespace App\Filament\Resources\ProjectResource\Pages;
use Illuminate\Support\Facades\Auth;

use App\Filament\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
{
    $data['created_by'] = Auth::id(); // Set created_by
    return $data;
}
}
