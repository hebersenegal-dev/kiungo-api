<?php
// database/seeders/StructureSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Structure;

class StructureSeeder extends Seeder
{
    public function run(): void
    {
        Structure::insert([
            [
                'name' => 'TechBridge Africa',
                'description' => 'A pan-African tech consultancy.',
                'logo' => null,
                'website' => 'https://techbridge.africa',
                'country_id' => 1,
            ],
            [
                'name' => 'GreenBuild Senegal',
                'description' => 'Sustainable construction specialists.',
                'logo' => null,
                'website' => 'https://greenbuild.sn',
                'country_id' => 1,
            ],
        ]);
    }
}
