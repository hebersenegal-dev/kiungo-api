<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\User;
use Illuminate\Support\Carbon;

class UserSubscriptionsChart extends ChartWidget
{
    protected static ?string $heading = 'Tendances d\'inscriptions des utilisateurs';

    public ?string $filter = 'all';

    protected function getFilters(): ?array
    {
        return [
            'all' => 'Tous',
            'structure' => 'Structures',
            'professional' => 'Professionnels',
        ];
    }

    protected function getData(): array
    {
        $query = User::query();

        if ($this->filter !== 'all') {
            $query->where('role', $this->filter);
        }

        $users = $query->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = $users->pluck('date')->map(fn ($date) => Carbon::parse($date)->translatedFormat('d M'))->toArray();
        $data = $users->pluck('count')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Inscriptions',
                    'data' => $data,
                    'backgroundColor' => '#fff',
                    'borderColor' => '#363636',
                    'fill' => false,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line'; // or 'bar' if you prefer
    }

    protected function getHeight(): ?string
    {
        return '300px';
    }
}
