<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Str;

class ProjectSeeder extends Seeder
{
    public function run()
    {
        // Clean up existing projects (optional)
        //Project::truncate();

        // Get users by role
        $admin = User::where('role', 'admin')->first();
        $structures = User::where('role', 'structure')->pluck('id')->toArray();
        $allUsers = User::pluck('id')->toArray();

        if (!$admin || empty($structures)) {
            $this->command->error("Please seed admin and structures first!");
            return;
        }

        for ($i = 1; $i <= 30; $i++) {
            $startDate = fake()->dateTimeBetween('-1 years', 'now');
            $endDate = (clone $startDate)->modify('+'.fake()->numberBetween(1, 6).' months');

            Project::create([
                'title' => fake()->catchPhrase,
                'description' => fake()->paragraph(4),
                'start_date' => $startDate,
                'end_date' => $endDate,
                'structure_id' => $structures[array_rand($structures)],
                'created_by' => $allUsers[array_rand($allUsers)],
                'website' => fake()->url,
                'is_verified' => true,
                'is_temp_created' => false,
                'approved_by' => $admin->id,
                'approved_at' => fake()->dateTimeBetween('-6 months', 'now'),
                'project_image' => 'projects/project_' . $i . '.jpg',
            ]);
        }
    }
}
