<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Project;

class ProjectSeeder extends Seeder
{
    public function run()
    {
        Project::create([
            'title' => 'Réhabilitation de l’école élémentaire',
            'description' => 'Projet de rénovation des bâtiments scolaires à Dakar.',
            'start_date' => '2024-01-10',
            'end_date' => '2024-06-30',
            'structure_id' => 3,
            'created_by' => 3,
            'is_verified' => true,
        ]);
    }
}
