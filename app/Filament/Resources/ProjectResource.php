<?php

namespace App\Filament\Resources;

use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\ProjectResource\Pages;
use App\Models\Project;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\{TextInput, DatePicker, Select, Textarea, FileUpload, Toggle};
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Columns\{TextColumn, BooleanColumn, ImageColumn};
use Filament\Tables\Filters\TernaryFilter;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationGroup = 'Projets';
    protected static ?string $navigationLabel = 'Projets verifiés';
    protected static ?string $pluralModelLabel = 'Projets';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('title')
                ->label('Titre')
                ->required()
                ->maxLength(255),

            Textarea::make('description')
                ->label('Description')
                ->required()
                ->maxLength(2000),

            DatePicker::make('start_date')
                ->label('Date de début')
                ->required(),

            DatePicker::make('end_date')
                ->label('Date de fin')
                ->required(),

            TextInput::make('website')
                ->url()
                ->label('Site web')
                ->maxLength(255),

            Select::make('structure_id')
                ->label('Structure')
                ->options(fn () => User::where('role', 'structure')
                    ->get()
                    ->mapWithKeys(fn ($user) => [$user->id => $user->company_name . ' ' . $user->last_name]))
                ->searchable()
                ->required(),

            FileUpload::make('project_image')
                ->directory('projects')
                ->image()
                ->label('Image')
                ->nullable(),

            Toggle::make('is_verified')
                ->label('Vérifié')
                ->inline(false)
                ->default(false)
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')
                ->label('Titre')
                ->searchable()
                ->sortable(),

            TextColumn::make('structure.company_name')
                ->label('Structure')
                ->sortable()
                ->searchable(),

            BooleanColumn::make('is_verified')
                ->label('Vérifié')
                ->sortable(),

            ImageColumn::make('project_image_url')
                ->label('Image')
                ->circular(),
        ])
        ->filters([
            TernaryFilter::make('is_verified')
                ->label('Statut de vérification'),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make()
                ->label('Supprimer les sélectionnés'),
        ])
        ->actions([
            Tables\Actions\DeleteAction::make()
                ->label('Supprimer'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
