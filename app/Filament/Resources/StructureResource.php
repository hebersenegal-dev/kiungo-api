<?php

namespace App\Filament\Resources;

use Illuminate\Support\Collection;
use App\Models\User;
use App\Models\Country;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\FileUpload;
use App\Filament\Resources\StructureResource\Pages;
use Illuminate\Support\Facades\Hash;
use App\Exports\StructuresExport;
use App\Imports\StructuresImport;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class StructureResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationLabel = 'Structures vérifiées';
    protected static ?string $navigationGroup = 'Structures';
    protected static ?string $pluralLabel = 'Structures vérifiées';
    protected static ?string $label = 'Structure vérifiée';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Informations de la structure')->schema([
                TextInput::make('company_name')
                    ->label('Nom de la structure')
                    ->required(),

                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),

                TextInput::make('password')
                    ->password()
                    ->label('Mot de passe')
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                    ->required(fn (string $context) => $context === 'create')
                    ->dehydrated(fn ($state) => filled($state)),

                Select::make('country_id')
                    ->label('Pays')
                    ->relationship('country', 'name')
                    ->required(),

                Select::make('sector')
                    ->label('Secteur')
                    ->options([
                      'societe_de_production' => 'Société de production',
     'salle_de_cinema' => 'Salle de cinéma',
     'centre_culturel' => 'Centre culturel',
     'bureau_accueil_tournages' => 'Bureau d\'accueil des tournages',
     'direction_cinematographie' => 'Direction de la cinématographie',
     'fonds_de_soutien' => 'Fonds de soutien',
     'diffuseur_chaine_tv_plateforme' => 'Diffuseur/chaîne de télévision/plateforme',
     'association_professionnels' => 'Association de professionnels',
     'festival' => 'Festival',
     'institution_publique' => 'Institution publique',
     'ecole_de_cinema' => 'École de cinéma'
                    ])
                    ->required(),

                TextInput::make('website')
                    ->label('Site Web')
                    ->url(),

                FileUpload::make('profile_picture')
                    ->label('Logo / Image')
                    ->image()
                    ->directory('structure-logos'),

                Toggle::make('is_verified')->label('Vérifié'),
            ])
        ]);
    }

    public static function table(Table $table): Table
{
    return $table
        ->query(User::where('role', 'structure')->where('is_verified', true))
        ->headerActions([
            // Export action
            Tables\Actions\Action::make('export')
                ->label('Exporter')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function ($livewire) {
                    $filters = $livewire->tableFilters;

                    return Excel::download(
                        new StructuresExport($filters),
                        'structures_' . now()->format('Y-m-d_H-i') . '.xlsx'
                    );
                }),

            // Import action
            Tables\Actions\Action::make('import')
                ->label('Importer')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    \Filament\Forms\Components\FileUpload::make('file')
                        ->label('Fichier Excel')
                        ->required()
                        ->acceptedFileTypes([
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ])
                        ->maxSize(10240) // 10MB
                        ->helperText('Téléchargez un fichier Excel (.xlsx, .xls). Téléchargez d\'abord le modèle pour voir le format requis.'),
                ])
                ->action(function (array $data) {
                    try {
                        $import = new StructuresImport();
                        Excel::import($import, $data['file']);

                        $errors = $import->getErrors();

                        if (empty($errors)) {
                            Notification::make()
                                ->title('Importation réussie')
                                ->body(sprintf(
                                    '%s nouvelles structures importées, %s mises à jour.',
                                    $import->getImportedCount(),
                                    $import->getUpdatedCount()
                                ))
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Importation avec erreurs')
                                ->body(sprintf(
                                    'Importation terminée avec %s erreur(s). %s nouvelles, %s mises à jour.',
                                    count($errors),
                                    $import->getImportedCount(),
                                    $import->getUpdatedCount()
                                ))
                                ->warning()
                                ->send();

                            // Log errors or store them for review
                            foreach ($errors as $error) {
                                \Log::warning('Excel import error (structures): ' . $error);
                            }
                        }
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Erreur d\'importation')
                            ->body('Une erreur est survenue lors de l\'importation: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->modalWidth('lg'),

            // Download template
            Tables\Actions\Action::make('downloadTemplate')
                ->label('Télécharger le modèle')
                ->icon('heroicon-o-document-arrow-down')
                ->action(function () {
                    $templateData = [

                        ['Ma Société de Prod', 'contact@masociete.com', 'password123', 'Société de production', 'Burundi', 'https://masociete.com', 'oui', 'oui', 'francais']

                    ];

                    $fileName = 'modele_import_structures_' . now()->format('Y-m-d') . '.xlsx';

                    return Excel::download(
                        new class($templateData) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings {
                            private $data;

                            public function __construct($data)
                            {
                                $this->data = $data;
                            }

                            public function array(): array
                            {
                                return $this->data;
                            }

                            public function headings(): array
                            {
                                return [
                                    'Nom de la structure',
                                    'Email',
                                    'Mot de passe',
                                    'Secteur',
                                    'Pays',
                                    'Site Web',
                                    'Vérifié',
                                    'Email vérifié',
                                    'Langue',
                                ];
                            }
                        },
                        $fileName
                    );
                }),
        ])
        ->columns([
            Tables\Columns\TextColumn::make('company_name')
                ->label('Structure')
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('email')
                ->label('Email')
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('sector')
                ->label('Secteur')
                ->sortable()
                ->searchable()
                ->formatStateUsing(function (string $state): string {
                    return [
                        'societe_de_production' => 'Société de production',
                        'salle_de_cinema' => 'Salle de cinéma',
                        'centre_culturel' => 'Centre culturel',
                        'bureau_accueil_tournages' => 'Bureau d\'accueil des tournages',
                        'direction_cinematographie' => 'Direction de la cinématographie',
                        'fonds_de_soutien' => 'Fonds de soutien',
                        'diffuseur_chaine_tv_plateforme' => 'Diffuseur/chaîne de télévision/plateforme',
                        'association_professionnels' => 'Association de professionnels',
                        'festival' => 'Festival',
                        'institution_publique' => 'Institution publique',
                        'ecole_de_cinema' => 'École de cinéma',
                    ][$state] ?? $state;
                }),

            Tables\Columns\TextColumn::make('country.name')
                ->label('Pays'),

            Tables\Columns\BooleanColumn::make('is_verified')
                ->label('Vérifié'),
        ])
        ->filters([
            SelectFilter::make('country_id')
                ->label('Pays')
                ->options(Country::all()->pluck('name', 'id')->toArray())
                ->searchable(),
            SelectFilter::make('sector')
                ->label('Secteur')
                ->options([
                    'societe_de_production' => 'Société de production',
                    'salle_de_cinema' => 'Salle de cinéma',
                    'centre_culturel' => 'Centre culturel',
                    'bureau_accueil_tournages' => 'Bureau d\'accueil des tournages',
                    'direction_cinematographie' => 'Direction de la cinématographie',
                    'fonds_de_soutien' => 'Fonds de soutien',
                    'diffuseur_chaine_tv_plateforme' => 'Diffuseur/chaîne de télévision/plateforme',
                    'association_professionnels' => 'Association de professionnels',
                    'festival' => 'Festival',
                    'institution_publique' => 'Institution publique',
                    'ecole_de_cinema' => 'École de cinéma'
                ])
                ->searchable(),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\BulkAction::make('markAsVerified')
                    ->label('Marquer comme vérifié')
                    ->icon('heroicon-o-check-circle')
                    ->action(function (Collection $records) {
                        $records->each(function ($record) {
                            $record->update([
                                'is_verified' => true,
                                'verified_at' => now(),
                                'verified_by' => auth()->id(),
                            ]);
                        });
                    })
                    ->deselectRecordsAfterCompletion(),
                Tables\Actions\BulkAction::make('markAsUnverified')
                    ->label('Marquer comme non vérifié')
                    ->icon('heroicon-o-x-circle')
                    ->action(function (Collection $records) {
                        $records->each(function ($record) {
                            $record->update([
                                'is_verified' => false,
                                'verified_at' => null,
                                'verified_by' => null,
                            ]);
                        });
                    })
                    ->deselectRecordsAfterCompletion(),
                Tables\Actions\BulkAction::make('verifyEmail')
                    ->label('Vérifier les emails')
                    ->icon('heroicon-o-envelope')
                    ->action(function (Collection $records) {
                        $records->each(function ($record) {
                            $record->update([
                                'email_verified_at' => now(),
                            ]);
                        });
                    })
                    ->deselectRecordsAfterCompletion(),
            ]),
        ]);
}

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStructures::route('/'),
            'create' => Pages\CreateStructure::route('/create'),
            'edit' => Pages\EditStructure::route('/{record}/edit'),
        ];
    }
}
