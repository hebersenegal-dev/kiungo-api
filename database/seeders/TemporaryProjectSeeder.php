<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\TemporaryProject;

class TemporaryProjectSeeder extends Seeder
{
    public function run()
    {
        TemporaryProject::create([
            'title' => 'Aménagement routier local',
            'description' => 'Travaux de voirie dans une commune rurale.',
            'start_date' => '2023-05-01',
            'end_date' => '2023-12-31',
            'temp_structure_id' => 1,
            'added_by' => 2,
            'is_verified' => false,
        ]);
    }
}
