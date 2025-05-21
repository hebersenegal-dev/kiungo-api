<?php

namespace App\Filament\Resources;

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
                        'Société de production' => 'Société de production',
                        'Salle de cinéma' => 'Salle de cinéma',
                        'Centre culturel' => 'Centre culturel',
                        'Bureau d\'accueil des tournages' => 'Bureau d\'accueil des tournages',
                        'Direction de la cinématographie' => 'Direction de la cinématographie',
                        'Fonds de soutien' => 'Fonds de soutien',
                        'Diffuseur/chaîne de télévision/plateforme' => 'Diffuseur/chaîne de télévision/plateforme',
                        'Association de professionnels' => 'Association de professionnels',
                        'Festival' => 'Festival',
                        'Institution publique' => 'Institution publique',
                        'École de cinéma' => 'École de cinéma',
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
                    ->searchable(),

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
                        'Société de production' => 'Société de production',
                        'Salle de cinéma' => 'Salle de cinéma',
                        'Centre culturel' => 'Centre culturel',
                        'Bureau d\'accueil des tournages' => 'Bureau d\'accueil des tournages',
                        'Direction de la cinématographie' => 'Direction de la cinématographie',
                        'Fonds de soutien' => 'Fonds de soutien',
                        'Diffuseur/chaîne de télévision/plateforme' => 'Diffuseur/chaîne de télévision/plateforme',
                        'Association de professionnels' => 'Association de professionnels',
                        'Festival' => 'Festival',
                        'Institution publique' => 'Institution publique',
                        'École de cinéma' => 'École de cinéma',
                    ])
                    ->searchable(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
