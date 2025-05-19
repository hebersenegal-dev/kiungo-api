<?php

namespace App\Filament\Widgets;
use App\Models\User;
use Filament\Widgets\ChartWidget;

class VerifiedVsUnverifiedUsersChart extends ChartWidget
{
    protected static ?string $heading = 'Comptes Vérifiés vs Non Vérifiés';
    protected static ?string $maxHeight = '300px';
    protected function getData(): array
   {
       $verified = User::where('is_verified', true)->count();
       $unverified = User::where('is_verified', false)->count();

       return [
           'datasets' => [
               [
                   'label' => 'Utilisateurs',
                   'data' => [$verified, $unverified],
                   'backgroundColor' => ['#878787', '#363636'], // green and red
               ],
           ],
           'labels' => ['Vérifiés', 'Non Vérifiés'],
       ];
   }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
   {
       return [
         'scales' => [ // Removes axis ticks (though not used for doughnut, safe fallback)
                'x' => ['display' => false],
                'y' => ['display' => false],
            ],
           'cutout' => '60%', // Reduces inner radius, making the donut "thinner"
           'plugins' => [
               'legend' => [
                   'position' => 'bottom',
                   'labels' => [
                       'boxWidth' => 10,
                       'padding' => 10,
                   ],
               ],
           ],
           'layout' => [
               'padding' => 10, // Adds spacing around chart
           ],
       ];
   }
}
