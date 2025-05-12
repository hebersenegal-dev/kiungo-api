<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Experience;

class ExperienceSeeder extends Seeder
{
    public function run()
    {
        Experience::create([
            'user_id' => 2,
            'structure_id' => 3,
            'project_id' => 1,
            'title' => 'Chef de chantier',
            'role' => 'Superviseure des travaux',
            'start_date' => '2024-01-15',
            'end_date' => '2024-06-15',
        ]);

        Experience::create([
            'user_id' => 2,
            'temp_structure_id' => 1,
            'temp_project_id' => 1,
            'title' => 'Conducteur de travaux',
            'role' => 'Coordination technique',
            'start_date' => '2023-05-15',
            'end_date' => '2023-12-15',
        ]);
    }
}
