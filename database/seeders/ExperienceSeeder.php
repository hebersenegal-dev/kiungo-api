<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Experience;
use App\Models\User;
use App\Models\Project;
use Illuminate\Support\Facades\Mail;

class ExperienceSeeder extends Seeder
{
    public function run()
    {

        Mail::fake();
        // Delete existing experiences
        //Experience::truncate();

        // Fetch all professionals (they have roles from the professions list)
        $professionals = User::where('role', 'professional')->get();

        // Fetch all projects with their associated structure
        $projects = Project::with('structure')->get();

        if ($professionals->isEmpty() || $projects->isEmpty()) {
            $this->command->error('Make sure User and Project seeders are run first.');
            return;
        }

        foreach ($projects as $project) {
            // Randomly assign 1–3 professionals per project
            $assignedUsers = $professionals->random(rand(1, 3));

            foreach ($assignedUsers as $user) {
                Experience::create([
                    'user_id' => $user->id,
                    'structure_id' => $project->structure_id,
                    'project_id' => $project->id,
                    'title' => fake()->jobTitle,
                    'role' => $user->profession, // from professions list
                    'start_date' => $project->start_date,
                    'end_date' => $project->end_date,
                ]);
            }
        }
    }
}
