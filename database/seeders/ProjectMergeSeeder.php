<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\ProjectMerge;

class ProjectMergeSeeder extends Seeder
{
    public function run()
    {
        ProjectMerge::create([
            'original_id' => 1,
            'official_id' => 1,
            'merged_by' => 3,
            'merged_at' => now(),
            'notes' => 'Projet officialisé.',
        ]);
    }
}
