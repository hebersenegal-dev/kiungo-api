<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Country;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StructuresImport implements ToCollection, WithHeadingRow, WithValidation
{
    protected $errors = [];
    protected $importedCount = 0;
    protected $updatedCount = 0;

    public function collection(Collection $rows)
    {
        foreach ($rows as $rowIndex => $row) {
            try {
                // Skip empty rows
                if (empty($row['email']) && empty($row['nom_de_la_structure'])) {
                    continue;
                }

                // Clean and validate email
                $email = $this->cleanEmail($row['email'] ?? '');
                if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[] = "Ligne " . ($rowIndex + 2) . ": Email invalide ou manquant - '{$row['email']}'";
                    continue;
                }

                // Find country by name (case-insensitive)
                $country = null;
                if (!empty($row['pays'])) {
                    $countryName = trim($row['pays']);
                    $country = Country::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($countryName) . '%'])->first();

                    if (!$country) {
                        $this->errors[] = "Ligne " . ($rowIndex + 2) . ": Pays non trouvé - '{$countryName}'";
                        // Continue anyway, country is required for structures
                    }
                }

                // Format sector
                $sectorKey = $this->getSectorKey($row['secteur'] ?? '');
                if (!$sectorKey) {
                    $this->errors[] = "Ligne " . ($rowIndex + 2) . ": Secteur invalide - '{$row['secteur']}'";
                    $sectorKey = 'societe_de_production'; // Default value
                }

                // Check if structure exists by email
                $structure = User::where('email', $email)->where('role', 'structure')->first();

                if ($structure) {
                    // Update existing structure
                    $updateData = [
                        'company_name' => trim($row['nom_de_la_structure'] ?? ''),
                        'sector' => $sectorKey,
                        'country_id' => $country ? $country->id : null,
                        'website' => $row['site_web'] ?? null,
                        'is_verified' => $this->parseBoolean($row['verifie'] ?? 'oui'), // Structures should be verified by default
                        'locale' => $this->getLocaleKey($row['langue'] ?? 'francais'),
                    ];

                    // Only update password if provided and not empty
                    if (!empty($row['mot_de_passe']) && trim($row['mot_de_passe']) !== '') {
                        $updateData['password'] = Hash::make(trim($row['mot_de_passe']));
                    }

                    $structure->update($updateData);
                    $this->updatedCount++;
                } else {
                    // Create new structure
                    $password = !empty($row['mot_de_passe']) && trim($row['mot_de_passe']) !== ''
                        ? trim($row['mot_de_passe'])
                        : Str::password(12); // Generate random password if not provided

                    User::create([
                        'company_name' => trim($row['nom_de_la_structure'] ?? ''),
                        'email' => $email,
                        'password' => Hash::make($password),
                        'role' => 'structure',
                        'sector' => $sectorKey,
                        'country_id' => $country ? $country->id : null,
                        'website' => $row['site_web'] ?? null,
                        'is_verified' => $this->parseBoolean($row['verifie'] ?? 'oui'), // Structures should be verified
                        'locale' => $this->getLocaleKey($row['langue'] ?? 'francais'),
                        'email_verified_at' => $this->parseBoolean($row['email_verifie'] ?? 'oui') ? now() : null,
                    ]);

                    $this->importedCount++;
                }
            } catch (\Exception $e) {
                $this->errors[] = "Ligne " . ($rowIndex + 2) . ": " . $e->getMessage();
            }
        }
    }

    public function rules(): array
    {
        return [
            'nom_de_la_structure' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'secteur' => 'required|string|max:255',
            'pays' => 'required|string|max:255',
            'site_web' => 'nullable|url|max:255',
            'verifie' => 'nullable|string|in:oui,non,yes,no,1,0,true,false',
            'email_verifie' => 'nullable|string|in:oui,non,yes,no,1,0,true,false',
            'langue' => 'nullable|string|in:francais,anglais,french,english,fr,en',
            'mot_de_passe' => 'nullable|string|min:6',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nom_de_la_structure.required' => 'Le nom de la structure est requis',
            'email.required' => 'L\'email est requis',
            'email.email' => 'L\'email doit être valide',
            'secteur.required' => 'Le secteur est requis',
            'pays.required' => 'Le pays est requis',
            'site_web.url' => 'Le site web doit être une URL valide',
            'mot_de_passe.min' => 'Le mot de passe doit avoir au moins 6 caractères',
        ];
    }

    private function cleanEmail($email)
    {
        $email = trim($email ?? '');
        $email = strtolower($email);
        return $email;
    }

    private function getSectorKey($sector)
    {
        $sector = trim($sector ?? '');
        if (empty($sector)) {
            return null;
        }

        $sectorsMap = [
            'Société de production' => 'societe_de_production',
            'société de production' => 'societe_de_production',
            'societe de production' => 'societe_de_production',
            'societe_de_production' => 'societe_de_production',

            'Salle de cinéma' => 'salle_de_cinema',
            'salle de cinéma' => 'salle_de_cinema',
            'salle de cinema' => 'salle_de_cinema',
            'salle_de_cinema' => 'salle_de_cinema',

            'Centre culturel' => 'centre_culturel',
            'centre culturel' => 'centre_culturel',
            'centre_culturel' => 'centre_culturel',

            'Bureau d\'accueil des tournages' => 'bureau_accueil_tournages',
            'bureau d\'accueil des tournages' => 'bureau_accueil_tournages',
            'bureau accueil tournages' => 'bureau_accueil_tournages',
            'bureau_accueil_tournages' => 'bureau_accueil_tournages',

            'Direction de la cinématographie' => 'direction_cinematographie',
            'direction de la cinématographie' => 'direction_cinematographie',
            'direction cinematographie' => 'direction_cinematographie',
            'direction_cinematographie' => 'direction_cinematographie',

            'Fonds de soutien' => 'fonds_de_soutien',
            'fonds de soutien' => 'fonds_de_soutien',
            'fonds_de_soutien' => 'fonds_de_soutien',

            'Diffuseur/chaîne de télévision/plateforme' => 'diffuseur_chaine_tv_plateforme',
            'diffuseur chaine television plateforme' => 'diffuseur_chaine_tv_plateforme',
            'diffuseur_chaine_tv_plateforme' => 'diffuseur_chaine_tv_plateforme',
            'diffuseur' => 'diffuseur_chaine_tv_plateforme',
            'chaîne télévision' => 'diffuseur_chaine_tv_plateforme',
            'plateforme' => 'diffuseur_chaine_tv_plateforme',

            'Association de professionnels' => 'association_professionnels',
            'association de professionnels' => 'association_professionnels',
            'association professionnels' => 'association_professionnels',
            'association_professionnels' => 'association_professionnels',

            'Festival' => 'festival',
            'festival' => 'festival',

            'Institution publique' => 'institution_publique',
            'institution publique' => 'institution_publique',
            'institution_publique' => 'institution_publique',

            'École de cinéma' => 'ecole_de_cinema',
            'école de cinéma' => 'ecole_de_cinema',
            'ecole de cinema' => 'ecole_de_cinema',
            'ecole_de_cinema' => 'ecole_de_cinema',
        ];

        // Try exact match first
        if (isset($sectorsMap[$sector])) {
            return $sectorsMap[$sector];
        }

        // Try lowercase match
        $lowercaseSector = strtolower($sector);
        if (isset($sectorsMap[$lowercaseSector])) {
            return $sectorsMap[$lowercaseSector];
        }

        // Try to find by partial match
        foreach ($sectorsMap as $key => $value) {
            if (strpos(strtolower($key), $lowercaseSector) !== false ||
                strpos($lowercaseSector, strtolower($key)) !== false) {
                return $value;
            }
        }

        // Return the original value normalized
        return strtolower(str_replace([' ', '-', '\'', '"', '/'], '_', $sector));
    }

    private function getLocaleKey($locale)
    {
        $locale = strtolower(trim($locale ?? ''));

        $localeMap = [
            'francais' => 'fr',
            'french' => 'fr',
            'fr' => 'fr',
            'français' => 'fr',
            'anglais' => 'en',
            'english' => 'en',
            'en' => 'en',
        ];

        return $localeMap[$locale] ?? 'fr';
    }

    private function parseBoolean($value)
    {
        $value = strtolower(trim($value ?? ''));

        $trueValues = ['oui', 'yes', '1', 'true', 'vrai', 'v'];
        $falseValues = ['non', 'no', '0', 'false', 'faux', 'f'];

        if (in_array($value, $trueValues)) {
            return true;
        }

        if (in_array($value, $falseValues)) {
            return false;
        }

        return true; // Default to true for structures
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getImportedCount()
    {
        return $this->importedCount;
    }

    public function getUpdatedCount()
    {
        return $this->updatedCount;
    }
}
