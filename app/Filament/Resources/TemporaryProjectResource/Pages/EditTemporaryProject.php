<?php

namespace App\Filament\Resources\TemporaryProjectResource\Pages;

use App\Filament\Resources\TemporaryProjectResource;
use App\Http\Controllers\Api\ProjectMergeController;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditTemporaryProject extends EditRecord
{
    protected static string $resource = TemporaryProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('merge')
                ->label('Créer un projet officiel et fusionner')
                ->icon('heroicon-o-check')
                ->form([
                    Textarea::make('notes')
                        ->label('Notes')
                        ->rows(3),
                ])
                ->visible(fn ($record) => !$record->is_verified)
                ->action(function (array $data) {
                    $this->save();

                    // Instantiate controller
                    $controller = app(ProjectMergeController::class);

                    // Merge additional data into the current request
                    $request = request()->merge([
                        'notes' => $data['notes'],
                        // No official_project_id needed – new one will be created in the controller
                    ]);

                    // Call the verify method directly
                    $response = $controller->verify($request, $this->record->id);

                    // Handle response and notify user
                    if ($response instanceof \Illuminate\Http\JsonResponse) {
                        $responseData = $response->getData();
                        if (isset($responseData->error)) {
                            Notification::make()
                                ->title('Erreur')
                                ->body($responseData->error)
                                ->danger()
                                ->send();
                        } elseif (isset($responseData->message)) {
                            Notification::make()
                                ->title('Succès')
                                ->body($responseData->message)
                                ->success()
                                ->send();
                        }
                    }
                })
                ->requiresConfirmation(),
        ];
    }
}
