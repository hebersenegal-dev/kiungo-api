<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\TemporaryStructure;

class TemporaryStructureSeeder extends Seeder
{
    public function run()
    {
        TemporaryStructure::create([
            'company_name' => 'InnovaFilm',
            'sector' => 'centre_culturel',
            'country_id' => 1,
            'website' => 'https://innovafilm.example',
            'added_by' => 2,
            'is_verified' => false,
        ]);
    }
}
