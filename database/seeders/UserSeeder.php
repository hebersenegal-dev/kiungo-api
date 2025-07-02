<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Country;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Clean up (optional, for reset)
        //User::truncate();

        // Get a random country for assignment
        $countryIds = Country::pluck('id')->toArray();

        // Create Admin
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'Kiungo',
            'email' => 'admin@kiungo.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'country_id' => $countryIds[array_rand($countryIds)],
            'is_verified' => true,
        ]);

        // Professions available
        $professions = [
            'realisateur', 'producteur', 'assistant_realisateur', 'scripte', 'charge_production',
            'directeur_photographie', 'chef_operateur', 'cadreur', 'cameraman', 'assistant_operateur',
            'premier_assistant_camera', 'etalonneur', 'ingenieur_son', 'chef_operateur_son', 'perchiste',
            'monteur_son', 'mixeur', 'designer_sonore', 'monteur_image', 'monteur_effets_speciaux',
            'motion_designer', 'scenariste', 'dialoguiste', 'documentariste', 'animateur_2d',
            'animateur_3d', 'storyboarder', 'concept_artist', 'journaliste_reporter_images',
            'regisseur_general', 'technicien_broadcasting', 'videaste', 'technicien_plateau',
            'operateur_lumiere', 'createur_contenu_video', 'specialiste_realite_virtuelle',
            'specialiste_realite_augmentee', 'responsable_chaine', 'community_manager_video'
        ];

        // Sectors for structures
        $sectors = [
            'societe_de_production', 'salle_de_cinema', 'centre_culturel',
            'bureau_accueil_tournages', 'direction_cinematographie', 'fonds_de_soutien',
            'diffuseur_chaine_tv_plateforme', 'association_professionnels', 'festival',
            'institution_publique', 'ecole_de_cinema'
        ];

        // 40 Professionals
        for ($i = 1; $i <= 40; $i++) {
            User::create([
                'first_name' => fake()->firstName,
                'last_name' => fake()->lastName,
                'email' => "pro{$i}@kiungo.test",
                'password' => Hash::make('password'),
                'role' => 'professional',
                'profession' => $professions[array_rand($professions)],
                'bio' => fake()->sentence(10),
                'country_id' => $countryIds[array_rand($countryIds)],
                'is_verified' => true,
                'verified_by' => $admin->id,
            ]);
        }

        // 10 Structures
        for ($i = 1; $i <= 10; $i++) {
            User::create([
                'company_name' => fake()->company,
                'email' => "structure{$i}@kiungo.test",
                'password' => Hash::make('password'),
                'role' => 'structure',
                'sector' => $sectors[array_rand($sectors)],
                'country_id' => $countryIds[array_rand($countryIds)],
                'website' => fake()->url,
                'bio' => fake()->sentence(12),
                'is_verified' => true,
                'verified_by' => $admin->id,
            ]);
        }
    }
}
