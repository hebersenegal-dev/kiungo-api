<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StructuresExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * Fetch structures
     */
    public function collection()
    {
        return User::with('country')
            ->where('role', 'structure')
            ->get();
    }

    /**
     * Excel headers (MUST match import headers)
     */
    public function headings(): array
    {
        return [
            'nom_de_la_structure',
            'email',
            'mot_de_passe',
            'secteur',
            'pays',
            'site_web',
            'verifie',
            'email_verifie',
            'langue',
        ];
    }

    /**
     * Map DB values → Excel-friendly values
     */
    public function map($structure): array
    {
        return [
            $structure->company_name,
            $structure->email,
            '', // never export passwords
            $this->getSectorLabel($structure->sector),
            optional($structure->country)->name,
            $structure->website ?? '',
            $structure->is_verified ? 'oui' : 'non',
            $structure->email_verified_at ? 'oui' : 'non',
            $this->getLocaleLabel($structure->locale),
        ];
    }

    /**
     * Convert sector key → human-readable label
     */
    private function getSectorLabel(?string $key): string
    {
        $map = [
            'societe_de_production' => 'Société de production',
            'salle_de_cinema' => 'Salle de cinéma',
            'centre_culturel' => 'Centre culturel',
            'bureau_accueil_tournages' => 'Bureau d\'accueil des tournages',
            'direction_cinematographie' => 'Direction de la cinématographie',
            'fonds_de_soutien' => 'Fonds de soutien',
            'diffuseur_chaine_tv_plateforme' => 'Diffuseur / chaîne TV / plateforme',
            'association_professionnels' => 'Association de professionnels',
            'festival' => 'Festival',
            'institution_publique' => 'Institution publique',
            'ecole_de_cinema' => 'École de cinéma',
        ];

        return $map[$key] ?? ucfirst(str_replace('_', ' ', $key ?? ''));
    }

    /**
     * Convert locale → import-friendly value
     */
    private function getLocaleLabel(?string $locale): string
    {
        return match ($locale) {
            'en' => 'english',
            default => 'francais',
        };
    }
}
