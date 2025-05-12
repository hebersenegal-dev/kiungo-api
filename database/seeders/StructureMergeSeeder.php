<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\StructureMerge;

class StructureMergeSeeder extends Seeder
{
    public function run()
    {
        StructureMerge::create([
            'original_id' => 1,
            'official_id' => 3,
            'merged_by' => 1,
            'merged_at' => now(),
            'notes' => 'Fusion validée par l’admin.',
        ]);
    }
}
