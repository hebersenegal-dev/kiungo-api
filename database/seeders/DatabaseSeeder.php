<?php

// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CountrySeeder::class,
            UserSeeder::class,
            StructureSeeder::class,
            ExperienceSeeder::class,
        ]);
    }
}
