<?php

namespace App\Filament\Resources;

use App\Models\TemporaryStructure;
use App\Models\StructureMerge;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class TemporaryStructureResource extends Resource
{
    protected static ?string $model = TemporaryStructure::class;

    protected static ?string $navigationLabel = 'Structures Temporaires';
    protected static ?string $navigationGroup = 'Structures';
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $pluralLabel = 'Structures Temporaires';
    protected static ?string $label = 'Structure Temporaire';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company_name')
                    ->searchable()
                    ->label('Nom de l\'entreprise'),

                    Tables\Columns\TextColumn::make('sector')
                        ->label('Secteur')
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

                Tables\Columns\TextColumn::make('verified_at')
                    ->dateTime()
                    ->label('Date de vérification'),
            ])
            ->actions([
                Action::make('verifyMerge')
                    ->label('Créer Structure Officielle & Fusionner')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn ($record) => !$record->is_verified)
                    ->form(fn (TemporaryStructure $record) => [
                        Forms\Components\TextInput::make('company_name')
                            ->required()
                            ->default($record->company_name)
                            ->label('Nom de l\'entreprise'),

                        Forms\Components\TextInput::make('email')
                            ->required()
                            ->email()
                            ->label('Email'),

                        Forms\Components\TextInput::make('password')
                            ->required()
                            ->password()
                            ->minLength(6)
                            ->label('Mot de passe'),

                            Forms\Components\Select::make('sector')
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
                    'ecole_de_cinema' => 'École de cinéma',
                ])
                ->required()
                ->label('Secteur'),

                        Forms\Components\Select::make('country_id')
                            ->relationship('country', 'name')
                            ->default($record->country_id)
                            ->required()
                            ->label('Pays'),

                        Forms\Components\TextInput::make('website')
                            ->default($record->website)
                            ->label('Site web'),

                        Forms\Components\Textarea::make('bio')
                            ->label('Bio'),

                        Forms\Components\FileUpload::make('profile_picture')
                            ->image()
                            ->directory('profiles')
                            ->label('Photo de profil'),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notes de fusion')
                            ->rows(3),
                    ])
                    ->action(function (TemporaryStructure $record, array $data) {
                        return DB::transaction(function () use ($record, $data) {
                            $user = Auth::user();

                            // Create official structure
                            $official = User::create([
                                'company_name' => $data['company_name'],
                                'email' => $data['email'],
                                'password' => bcrypt($data['password']),
                                'role' => 'structure',
                                'sector' => $data['sector'] ?? null,
                                'country_id' => $data['country_id'],
                                'website' => $data['website'] ?? null,
                                'bio' => $data['bio'] ?? null,
                                'profile_picture' => $data['profile_picture'] ?? null,
                                'temporary_structure_id' => $record->id,
                                'is_verified' => true,
                                'verified_at' => now(),
                                'verified_by' => $user->id,
                            ]);

                            // Update all experiences linked to this temporary structure
                            DB::table('experiences')
                                ->where('temp_structure_id', $record->id)
                                ->update([
                                    'structure_id' => $official->id,
                                    'temp_structure_id' => null
                                ]);

                            // Mark temporary structure as verified
                            $record->update([
                                'is_verified' => true,
                                'verified_at' => now(),
                                'verified_by' => $user->id,
                            ]);

                            // Log the merge
                            StructureMerge::create([
                                'original_id' => $record->id,
                                'official_id' => $official->id,
                                'merged_by' => $user->id,
                                'merged_at' => now(),
                                'notes' => $data['notes'] ?? null,
                            ]);

                            Notification::make()
                                ->title('Structure fusionnée avec succès')
                                ->body('La structure et toutes ses expériences associées ont été migrées.')
                                ->success()
                                ->send();
                        });
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Confirmer la fusion')
                    ->modalDescription('Êtes-vous sûr de vouloir créer une structure officielle et fusionner les données ?'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->label('Supprimer les sélectionnés'),
            ]);
    }

    public static function form(Form $form): Form
  {
      return $form->schema([
          Forms\Components\TextInput::make('company_name')
              ->required()
              ->label('Nom de l\'entreprise'),

          Forms\Components\Select::make('sector')
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
                  'ecole_de_cinema' => 'École de cinéma',
              ])
              ->required()
              ->label('Secteur'),

          Forms\Components\Select::make('country_id')
              ->relationship('country', 'name')
              ->required()
              ->label('Pays'),

          Forms\Components\TextInput::make('website')
              ->url()
              ->label('Site web'),
      ]);
  }


    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\TemporaryStructureResource\Pages\ListTemporaryStructures::route('/'),
            'edit' => \App\Filament\Resources\TemporaryStructureResource\Pages\EditTemporaryStructure::route('/{record}/edit'),
        ];
    }
}
