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
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\DatePicker;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationLabel = 'Professionnels';
    protected static ?string $pluralLabel = 'Professionnels';
    protected static ?string $label = 'Professionnel';

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
                Select::make('role')
                    ->label('Rôle')
                    ->default('professional')
                    ->options([

                        'professional' => 'Professionnel',
                    ])
                    ->required(),


                Toggle::make('is_verified')
                    ->label('Vérifié'),


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
                        'Other' => 'Autre',
                    ])
                    ->required(),

                TextInput::make('url')
                    ->label('Lien')
                    ->url()
                    ->required(),
            ])
            ->addActionLabel('Ajouter un réseau')
            ->columns(2),
    ]),



        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(User::where('role', 'professional')) // Only professionals
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

                BooleanColumn::make('is_verified')
                    ->label('Vérifié'),
                TextColumn::make('experiences_count')
                    ->label('Expériences')
                    ->counts('experiences')
                    ->formatStateUsing(fn ($state) => new HtmlString(
                        "<span class='font-medium'>{$state}</span> expérience" . ($state > 1 ? 's' : '')
                    )),



            ])
            ->filters([
                TernaryFilter::make('is_verified')
                    ->label('Vérification')
                    ->trueLabel('Vérifiés')
                    ->falseLabel('Non vérifiés')
                    ->default(null),
            ])
            ->actions([
                //Tables\Actions\EditAction::make(),
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
          'role' => $experience->role,
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
            ])
            ->bulkActions([
                DeleteBulkAction::make()
                    ->label('Supprimer les sélectionnés'),
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
