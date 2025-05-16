<?php

namespace App\Filament\Widgets;

use App\Models\TemporaryProject;
use App\Models\TemporaryStructure;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [

            Stat::make('Professionnels non vérifiés', User::where('role', 'professional')->where('is_verified', false)->count()),
            Stat::make('Structures non vérifiées', User::where('role', 'structure')->where('is_verified', false)->count()),

            Stat::make('Professionnels', User::where('role', 'professional')->count()),
            Stat::make('Structures', User::where('role', 'structure')->count()),


            Stat::make('Structures temporaires', TemporaryStructure::count()),
            Stat::make('Projets temporaires', TemporaryProject::count()),
        ];
    }
}
