<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TemporaryProjectResource\Pages;
use App\Models\TemporaryProject;
use App\Models\Project;
use App\Models\ProjectMerge;
use App\Models\StructureMerge;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class TemporaryProjectResource extends Resource
{
    protected static ?string $model = TemporaryProject::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Projets temporaires';

    protected static ?string $navigationGroup = 'Projets';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tempStructure.company_name')
                    ->label('Structure')
                    ->searchable(),
                Tables\Columns\BooleanColumn::make('is_verified')
                    ->label('Vérifié'),
                Tables\Columns\TextColumn::make('verified_at')
                    ->label('Date de vérification')
                    ->dateTime(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_verified')
                    ->label('Vérifié'),
            ])
            ->actions([
                Action::make('merge')
                    ->label('Fusionner')
                    ->icon('heroicon-o-check')
                    ->visible(fn ($record) => !$record->is_verified && in_array(Auth::user()->role, ['admin', 'structure']))
                    ->action(function (TemporaryProject $record, array $data) {
                        $merge = StructureMerge::where('original_id', $record->temp_structure_id)->first();

                        if (! $merge) {
                            Notification::make()
                                ->title('Structure non fusionnée')
                                ->body('La structure temporaire liée doit être fusionnée avant de pouvoir vérifier ce projet.')
                                ->danger()
                                ->send();
                            return;
                        }

                        $project = Project::create([
                            'title' => $record->title,
                            'description' => $record->description,
                            'start_date' => $record->start_date,
                            'end_date' => $record->end_date,
                            'structure_id' => $merge->official_id,
                            'created_by' => Auth::id(),
                            'website' => $record->website,
                            'project_image' => $record->project_image,
                            'is_verified' => true,
                            'is_temp_created' => true,
                            'approved_by' => Auth::id(),
                            'approved_at' => now(),
                        ]);

                        $record->update([
                            'is_verified' => true,
                            'verified_at' => now(),
                            'verified_by' => Auth::id(),
                        ]);

                        ProjectMerge::create([
                            'original_id' => $record->id,
                            'official_id' => $project->id,
                            'merged_by' => Auth::id(),
                            'merged_at' => now(),
                            'notes' => $data['notes'] ?? null,
                        ]);

                        Notification::make()
                            ->title('Fusion réussie')
                            ->success()
                            ->send();
                    })
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes de fusion')
                            ->rows(3)
                            ->required(false),
                    ])
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->label('Supprimer les sélectionnés'),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Titre')
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->required(),
                Forms\Components\DatePicker::make('start_date')
                    ->label('Date de début')
                    ->required(),
                Forms\Components\DatePicker::make('end_date')
                    ->label('Date de fin')
                    ->required(),
                Forms\Components\TextInput::make('website')
                    ->label('Site web')
                    ->url(),
                Forms\Components\FileUpload::make('project_image')
                    ->label('Image')
                    ->disk('public')
                    ->directory('projects'),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTemporaryProjects::route('/'),
            'edit' => Pages\EditTemporaryProject::route('/{record}/edit'),
        ];
    }
}
