<?php
// database/seeders/CountrySeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        Country::insert([
            ['name' => 'Senegal', 'country_code' => 'SN'],
            ['name' => 'Ivory Coast', 'country_code' => 'CI'],
            ['name' => 'Mali', 'country_code' => 'ML'],
        ]);
    }
}
