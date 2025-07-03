<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;
use App\Models\User;
use App\Models\TemporaryStructure;
use App\Models\TemporaryProject;
use App\Models\Project;
use App\Models\Experience;

class DatabaseSeeder extends Seeder
{
    public function run():void
    {

    $this->call([
        CountrySeeder::class,
        UserSeeder::class,
        TemporaryStructureSeeder::class,
        ProjectSeeder::class,
        TemporaryProjectSeeder::class,
        ExperienceSeeder::class,
        StructureMergeSeeder::class,
        ProjectMergeSeeder::class,
    ]);
    }
}
