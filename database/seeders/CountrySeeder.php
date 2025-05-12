<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Country;

class CountrySeeder extends Seeder
{
    public function run()
    {
        Country::insert([
            ['name' => 'Sénégal'],
            ['name' => 'Côte d\'Ivoire'],
            ['name' => 'Mali'],
        ]);
    }
}
