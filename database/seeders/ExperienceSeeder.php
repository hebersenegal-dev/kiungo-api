<?php
// database/seeders/ExperienceSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Experience;

class ExperienceSeeder extends Seeder
{
    public function run(): void
    {
        Experience::create([
            'user_id' => 1,
            'title' => 'Mobile App Development',
            'role' => 'Lead Developer',
            'project_link' => 'https://playstore.com/sampleapp',
            'start_year' => 2021,
            'end_year' => 2022,
        ]);
    }
}
