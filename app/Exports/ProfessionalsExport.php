<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Country;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProfessionalsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * Fetch professionals
     */
    public function collection()
    {
        return User::with('country')
            ->where('role', 'professional')
            ->get();
    }

    /**
     * Excel headers (MUST match import headers)
     */
    public function headings(): array
    {
        return [
            'prenom',
            'nom',
            'email',
            'mot_de_passe',
            'profession',
            'pays',
            'site_web',
            'biographie',
            'verifie',
            'email_verifie',
            'langue',
        ];
    }

    /**
     * Map DB values → Excel-friendly values
     */
    public function map($user): array
    {
        return [
            $user->first_name,
            $user->last_name,
            $user->email,
            '', // never export passwords
            $this->getProfessionLabel($user->profession),
            optional($user->country)->name,
            $user->website ?? '',
            $user->bio ?? '',
            $user->is_verified ? 'oui' : 'non',
            $user->email_verified_at ? 'oui' : 'non',
            $this->getLocaleLabel($user->locale),
        ];
    }

    /**
     * Convert DB profession key → human label
     */
    private function getProfessionLabel($key): string
    {
        $map = [
            'realisateur' => 'Réalisateur',
            'producteur' => 'Producteur',
            'assistant_realisateur' => 'Assistant Réalisateur',
            'scripte' => 'Scripte',
            'charge_production' => 'Chargé de production',
            'directeur_photographie' => 'Directeur de la photographie',
            'chef_operateur' => 'Chef opérateur',
            'cadreur' => 'Cadreur',
            'cameraman' => 'Cameraman',
            'assistant_operateur' => 'Assistant opérateur',
            'premier_assistant_camera' => '1er assistant caméra',
            'etalonneur' => 'Étalonneur',
            'ingenieur_son' => 'Ingénieur du son',
            'chef_operateur_son' => 'Chef opérateur son',
            'perchiste' => 'Perchiste',
            'monteur_son' => 'Monteur son',
            'mixeur' => 'Mixeur',
            'designer_sonore' => 'Designer sonore',
            'monteur_image' => 'Monteur image',
            'monteur_effets_speciaux' => 'Monteur effets spéciaux',
            'motion_designer' => 'Motion designer',
            'scenariste' => 'Scénariste',
            'dialoguiste' => 'Dialoguiste',
            'documentariste' => 'Documentariste',
            'animateur_2d' => 'Animateur 2D',
            'animateur_3d' => 'Animateur 3D',
            'storyboarder' => 'Storyboarder',
            'concept_artist' => 'Concept artist',
            'journaliste_reporter_images' => 'Journaliste reporter d\'images',
            'regisseur_general' => 'Régisseur général',
            'technicien_broadcasting' => 'Technicien broadcasting',
            'videaste' => 'Vidéoaste',
            'technicien_plateau' => 'Technicien plateau',
            'operateur_lumiere' => 'Opérateur lumière',
            'createur_contenu_video' => 'Créateur de contenu vidéo',
            'specialiste_realite_virtuelle' => 'Spécialiste réalité virtuelle',
            'specialiste_realite_augmentee' => 'Spécialiste réalité augmentée',
            'responsable_chaine' => 'Responsable de chaîne',
            'community_manager_video' => 'Community manager vidéo',
            'other' => 'Autre',
        ];

        return $map[$key] ?? 'Autre';
    }

    /**
     * Convert locale key → import-friendly value
     */
    private function getLocaleLabel($locale): string
    {
        return match ($locale) {
            'en' => 'english',
            default => 'francais',
        };
    }
}
