<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Facades\Hash;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Filament\Tables\Filters\SelectFilter;
use App\Exports\ProfessionalsExport;
use App\Imports\ProfessionalsImport;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;


class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationLabel = 'Professionnels';
    protected static ?string $pluralLabel = 'Professionnels';
    protected static ?string $label = 'Professionnel';

    public static function getFormattedRole(string $role): string
{
    return match ($role) {
        'realisateur' => 'Réalisateur',
        'producteur' => 'Producteur',
        'assistant_realisateur' => 'Assistant Réalisateur',
        'scripte' => 'Scripte',
        'charge_production' => 'Chargé de production',
        'directeur_photographie' => 'Directeur de la photographie',
        'chef_operateur' => 'Chef opérateur',
        'cadreur' => 'Cadreur',
        'cameraman' => 'Cameraman',
        'assistant_operateur' => 'Assistant opérateur',
        'premier_assistant_camera' => '1er assistant caméra',
        'etalonneur' => 'Étalonneur',
        'ingenieur_son' => 'Ingénieur du son',
        'chef_operateur_son' => 'Chef opérateur son',
        'perchiste' => 'Perchiste',
        'monteur_son' => 'Monteur son',
        'mixeur' => 'Mixeur',
        'designer_sonore' => 'Designer sonore',
        'monteur_image' => 'Monteur image',
        'monteur_effets_speciaux' => 'Monteur effets spéciaux',
        'motion_designer' => 'Motion designer',
        'scenariste' => 'Scénariste',
        'dialoguiste' => 'Dialoguiste',
        'documentariste' => 'Documentariste',
        'animateur_2d' => 'Animateur 2D',
        'animateur_3d' => 'Animateur 3D',
        'storyboarder' => 'Storyboarder',
        'concept_artist' => 'Concept artist',
        'journaliste_reporter_images' => 'Journaliste reporter d’images',
        'regisseur_general' => 'Régisseur général',
        'technicien_broadcasting' => 'Technicien broadcasting',
        'videaste' => 'Vidéoaste',
        'technicien_plateau' => 'Technicien plateau',
        'operateur_lumiere' => 'Opérateur lumière',
        'createur_contenu_video' => 'Créateur de contenu vidéo',
        'specialiste_realite_virtuelle' => 'Spécialiste réalité virtuelle',
        'specialiste_realite_augmentee' => 'Spécialiste réalité augmentée',
        'responsable_chaine' => 'Responsable de chaîne',
        'community_manager_video' => 'Community manager vidéo',
        default => ucfirst(str_replace('_', ' ', $role)),
    };
}


public static function form(Form $form): Form
{
    return $form->schema([
        Section::make('Informations utilisateur')->schema([
            TextInput::make('first_name')
                ->label('Prénom')
                ->required(),
            TextInput::make('last_name')
                ->label('Nom')
                ->required(),
            TextInput::make('email')
                ->email()
                ->required()
                ->unique(ignoreRecord: true)
                ->label('Email'),
            TextInput::make('password')
                ->label('Mot de passe')
                ->password()
                ->revealable()
                ->required(fn (string $context): bool => $context === 'create')
                ->dehydrated(fn ($state) => filled($state))
                ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                ->maxLength(255),
            TextInput::make('password_confirmation')
                ->label('Confirmer le mot de passe')
                ->password()
                ->revealable()
                ->requiredWith('password')
                ->same('password')
                ->dehydrated(false),
            Select::make('role')
                ->label('Rôle')
                ->default('professional')
                ->options([
                    'professional' => 'Professionnel',
                ])
                ->required()
                ->disabled(), // You can disable this if it's always professional
            Select::make('profession')
                ->label('Profession principale')
                ->options([
                    'realisateur' => 'Réalisateur',
                    'producteur' => 'Producteur',
                    'assistant_realisateur' => 'Assistant Réalisateur',
                    'scripte' => 'Scripte',
                    'charge_production' => 'Chargé de production',
                    'directeur_photographie' => 'Directeur de la photographie',
                    'chef_operateur' => 'Chef opérateur',
                    'cadreur' => 'Cadreur',
                    'cameraman' => 'Cameraman',
                    'assistant_operateur' => 'Assistant opérateur',
                    'premier_assistant_camera' => '1er assistant caméra',
                    'etalonneur' => 'Étalonneur',
                    'ingenieur_son' => 'Ingénieur du son',
                    'chef_operateur_son' => 'Chef opérateur son',
                    'perchiste' => 'Perchiste',
                    'monteur_son' => 'Monteur son',
                    'mixeur' => 'Mixeur',
                    'designer_sonore' => 'Designer sonore',
                    'monteur_image' => 'Monteur image',
                    'monteur_effets_speciaux' => 'Monteur effets spéciaux',
                    'motion_designer' => 'Motion designer',
                    'scenariste' => 'Scénariste',
                    'dialoguiste' => 'Dialoguiste',
                    'documentariste' => 'Documentariste',
                    'animateur_2d' => 'Animateur 2D',
                    'animateur_3d' => 'Animateur 3D',
                    'storyboarder' => 'Storyboarder',
                    'concept_artist' => 'Concept artist',
                    'journaliste_reporter_images' => 'Journaliste reporter d\'images',
                    'regisseur_general' => 'Régisseur général',
                    'technicien_broadcasting' => 'Technicien broadcasting',
                    'videaste' => 'Vidéoaste',
                    'technicien_plateau' => 'Technicien plateau',
                    'operateur_lumiere' => 'Opérateur lumière',
                    'createur_contenu_video' => 'Créateur de contenu vidéo',
                    'specialiste_realite_virtuelle' => 'Spécialiste réalité virtuelle',
                    'specialiste_realite_augmentee' => 'Spécialiste réalité augmentée',
                    'responsable_chaine' => 'Responsable de chaîne',
                    'community_manager_video' => 'Community manager vidéo',
                ])
                ->required()
                ->searchable(),
            Select::make('country_id')
                ->label('Pays')
                ->relationship('country', 'name')
                ->searchable()
                ->preload()
                ->required(),
            Textarea::make('bio')
                ->label('Biographie')
                ->rows(3)
                ->nullable(),
            FileUpload::make('profile_picture')
                ->label('Photo de profil')
                ->image()
                ->directory('profile-pictures')
                ->maxSize(2048) // 2MB
                ->nullable(),
            Select::make('locale')
                ->label('Langue')
                ->options([
                    'fr' => 'Français',
                    'en' => 'English',
                ])
                ->default('fr'),
            Toggle::make('is_verified')
                ->label('Vérifié'),
            Toggle::make('email_verified_at')
                ->label('Email vérifié')
                ->afterStateUpdated(function ($state, $set) {
                    $set('email_verified_at', $state ? now() : null);
                })
                ->dehydrateStateUsing(fn ($state) => $state ? now() : null)
                ->dehydrated(false),
        ]),

        Section::make('Réseaux sociaux')
            ->schema([
                Repeater::make('social_medias')
                    ->label('Réseaux sociaux')
                    ->schema([
                        Select::make('platform')
                            ->label('Plateforme')
                            ->options([
                                'Facebook' => 'Facebook',
                                'Instagram' => 'Instagram',
                                'X' => 'X (Twitter)',
                                'LinkedIn' => 'LinkedIn',
                                'YouTube' => 'YouTube',
                            ])->required(),
                        TextInput::make('url')
                            ->label('Lien')
                            ->url()
                            ,
                    ])
                    ->addActionLabel('Ajouter un réseau')
                    ->columns(2),
            ]),
    ]);
}

public static function table(Table $table): Table
{
    return $table
        ->query(User::where('role', 'professional'))
        ->headerActions([
            // Export action
            Tables\Actions\Action::make('export')
                ->label('Exporter')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function ($livewire) {
                    $filters = $livewire->tableFilters;

                    return Excel::download(
                        new ProfessionalsExport($filters),
                        'professionnels_' . now()->format('Y-m-d_H-i') . '.xlsx'
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
                        $import = new ProfessionalsImport();
                        Excel::import($import, $data['file']);

                        $errors = $import->getErrors();

                        if (empty($errors)) {
                            Notification::make()
                                ->title('Importation réussie')
                                ->body(sprintf(
                                    '%s nouveaux professionnels importés, %s mis à jour.',
                                    $import->getImportedCount(),
                                    $import->getUpdatedCount()
                                ))
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Importation avec erreurs')
                                ->body(sprintf(
                                    'Importation terminée avec %s erreur(s). %s nouveaux, %s mis à jour.',
                                    count($errors),
                                    $import->getImportedCount(),
                                    $import->getUpdatedCount()
                                ))
                                ->warning()
                                ->send();

                            // Log errors or store them for review
                            foreach ($errors as $error) {
                                \Log::warning('Excel import error: ' . $error);
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

                        ['John', 'Doe', 'john@test.com', 'password123', 'Réalisateur', 'Burundi', 'Biographie ici', 'oui', 'oui', 'francais']
                    ];

                    $fileName = 'modele_import_professionnels_' . now()->format('Y-m-d') . '.xlsx';

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
                                    'Prénom',
                                    'Nom',
                                    'Email',
                                    'Mot de passe',
                                    'Profession',
                                    'Pays',

                                    'Biographie',
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
            TextColumn::make('first_name')
                ->label('Prénom')
                ->sortable()
                ->searchable(),
            TextColumn::make('last_name')
                ->label('Nom')
                ->sortable()
                ->searchable(),
            TextColumn::make('email')
                ->label('Email')
                ->sortable()
                ->searchable(),
            TextColumn::make('profession')
                ->label('Profession')
                ->formatStateUsing(fn ($state): string => self::getFormattedRole($state))
                ->sortable()
                ->searchable(),
            TextColumn::make('country.name')
                ->label('Pays')
                ->sortable(),
            BooleanColumn::make('is_verified')
                ->label('Vérifié')
                ->sortable(),
            TextColumn::make('locale')
                ->label('Langue')
                ->formatStateUsing(fn ($state): string => $state === 'fr' ? '🇫🇷 FR' : '🇬🇧 EN')
                ->sortable(),
            TextColumn::make('created_at')
                ->label('Créé le')
                ->dateTime('d/m/Y H:i')
                ->sortable(),
        ])
        ->filters([
            TernaryFilter::make('is_verified')
                ->label('Vérification')
                ->trueLabel('Vérifiés')
                ->falseLabel('Non vérifiés')
                ->default(null),
            TernaryFilter::make('email_verified_at')
                ->label('Email vérifié')
                ->trueLabel('Email vérifié')
                ->falseLabel('Email non vérifié')
                ->default(null),
            Tables\Filters\SelectFilter::make('country_id')
                ->label('Pays')
                ->relationship('country', 'name')
                ->searchable()
                ->preload(),
            Tables\Filters\SelectFilter::make('profession')
                ->label('Profession')
                ->multiple()
                ->options([
                    'realisateur' => 'Réalisateur',
                    'producteur' => 'Producteur',
                    'assistant_realisateur' => 'Assistant Réalisateur',
                    'scripte' => 'Scripte',
                    'charge_production' => 'Chargé de production',
                    'directeur_photographie' => 'Directeur de la photographie',
                    'chef_operateur' => 'Chef opérateur',
                    'cadreur' => 'Cadreur',
                    'cameraman' => 'Cameraman',
                    'assistant_operateur' => 'Assistant opérateur',
                    'premier_assistant_camera' => '1er assistant caméra',
                    'etalonneur' => 'Étalonneur',
                    'ingenieur_son' => 'Ingénieur du son',
                    'chef_operateur_son' => 'Chef opérateur son',
                    'perchiste' => 'Perchiste',
                    'monteur_son' => 'Monteur son',
                    'mixeur' => 'Mixeur',
                    'designer_sonore' => 'Designer sonore',
                    'monteur_image' => 'Monteur image',
                    'monteur_effets_speciaux' => 'Monteur effets spéciaux',
                    'motion_designer' => 'Motion designer',
                    'scenariste' => 'Scénariste',
                    'dialoguiste' => 'Dialoguiste',
                    'documentariste' => 'Documentariste',
                    'animateur_2d' => 'Animateur 2D',
                    'animateur_3d' => 'Animateur 3D',
                    'storyboarder' => 'Storyboarder',
                    'concept_artist' => 'Concept artist',
                    'journaliste_reporter_images' => 'Journaliste reporter d\'images',
                    'regisseur_general' => 'Régisseur général',
                    'technicien_broadcasting' => 'Technicien broadcasting',
                    'videaste' => 'Vidéoaste',
                    'technicien_plateau' => 'Technicien plateau',
                    'operateur_lumiere' => 'Opérateur lumière',
                    'createur_contenu_video' => 'Créateur de contenu vidéo',
                    'specialiste_realite_virtuelle' => 'Spécialiste réalité virtuelle',
                    'specialiste_realite_augmentee' => 'Spécialiste réalité augmentée',
                    'responsable_chaine' => 'Responsable de chaîne',
                    'community_manager_video' => 'Community manager vidéo',
                ]),
            Filter::make('created_at')
                ->label('Date de création')
                ->form([
                    DatePicker::make('created_from')
                        ->label('Du'),
                    DatePicker::make('created_until')
                        ->label('Au'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['created_from'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                        )
                        ->when(
                            $data['created_until'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                        );
                }),
            Filter::make('has_experiences')
                ->label('Avec expériences')
                ->toggle()
                ->query(fn (Builder $query): Builder => $query->has('experiences')),
            Filter::make('has_awards')
                ->label('Avec récompenses')
                ->toggle()
                ->query(fn (Builder $query): Builder => $query->has('awards')),
        ])
        ->actions([
            Action::make('view_experiences')
                ->label('Voir expériences')
                ->icon('heroicon-o-briefcase')
                ->modalHeading(fn (User $record) => "Expériences de {$record->first_name} {$record->last_name}")
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Fermer')
                ->form(fn (User $record) => [
                    Repeater::make('experiences')
                        ->label(false)
                        ->schema([
                            Forms\Components\Placeholder::make('header')
                                ->content(fn ($get) =>
                                    $get('title') .
                                    ($get('is_verified') ? ' ✅' : ' ⏳')
                                )
                                ->columnSpanFull()
                                ->disableLabel(),

                            TextInput::make('role')->label('Rôle')->disabled(),
                            TextInput::make('structure')->label('Structure')->disabled(),
                            TextInput::make('project')->label('Projet')->disabled(),
                            DatePicker::make('start_date')->label('Début')->disabled(),
                            DatePicker::make('end_date')->label('Fin')->disabled(),
                        ])
                        ->default(fn () => $record->experiences->map(function ($experience) {
                            return [
                                'title' => $experience->title,
                                'role' => self::getFormattedRole($experience->role),
                                'structure' => $experience->structure->company_name ?? $experience->tempStructure->company_name ?? '—',
                                'project' => $experience->project->title ?? $experience->tempProject->title ?? '—',
                                'start_date' => $experience->start_date,
                                'end_date' => $experience->end_date,
                                'is_verified' => $experience->structure_id !== null && $experience->project_id !== null,
                            ];
                        })->toArray())
                        ->columns(2)
                        ->disabled(),
                ]),
            Action::make('view_awards')
                ->label('Voir récompenses')
                ->icon('heroicon-o-trophy')
                ->modalHeading(fn (User $record) => "Récompenses de {$record->first_name} {$record->last_name}")
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Fermer')
                ->form(fn (User $record) => [
                    Repeater::make('awards')
                        ->label(false)
                        ->schema([
                            TextInput::make('name')->label('Nom')->disabled(),
                            TextInput::make('year')->label('Année')->disabled(),
                            TextInput::make('category')->label('Catégorie')->disabled(),
                            TextInput::make('festival')->label('Festival')->disabled(),
                            Textarea::make('description')->label('Description')->disabled()->rows(2),
                        ])
                        ->default(fn () => $record->awards->map(function ($award) {
                            return [
                                'name' => $award->name,
                                'year' => $award->year,
                                'category' => $award->category,
                                'festival' => $award->festival,
                                'description' => $award->description,
                            ];
                        })->toArray())
                        ->columns(2)
                        ->disabled(),
                ]),
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make()
                    ->label('Supprimer les sélectionnés'),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
