<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Country;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ProfessionalsImport implements ToCollection, WithHeadingRow
{
    use Importable, SkipsFailures;

    protected $errors = [];
    protected $importedCount = 0;
    protected $updatedCount = 0;
    protected $skippedCount = 0;
    protected $validationErrors = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 because heading row is row 1

            try {
                // Validate the row data
                $validator = Validator::make($row->toArray(), [
                    'prenom' => 'required|string|max:255',
                    'nom' => 'required|string|max:255',
                    'email' => 'required|email|max:255',
                    'profession' => 'required|string|max:255',
                    'pays' => 'nullable|string|max:255',
                    'site_web' => 'nullable|url|max:255',
                    'biographie' => 'nullable|string',
                    'verifie' => 'nullable|string|in:oui,non',
                    'email_verifie' => 'nullable|string|in:oui,non',
                    'langue' => 'nullable|string|in:francais,anglais,french,english,fr,en',
                    'mot_de_passe' => 'nullable|string|min:6',
                ], [
                    'email.required' => 'L\'email est requis',
                    'email.email' => 'L\'email doit être valide',
                    'prenom.required' => 'Le prénom est requis',
                    'nom.required' => 'Le nom est requis',
                    'profession.required' => 'La profession est requise',
                    'site_web.url' => 'Le site web doit être une URL valide',
                    'mot_de_passe.min' => 'Le mot de passe doit avoir au moins 6 caractères',
                ]);

                if ($validator->fails()) {
                    $errors = $validator->errors()->all();
                    $this->validationErrors[] = "Ligne $rowNumber: " . implode(', ', $errors);
                    $this->skippedCount++;
                    continue;
                }

                // Check for duplicate email
                if (User::where('email', $row['email'])->where('role', 'professional')->exists()) {
                    $this->validationErrors[] = "Ligne $rowNumber: L'email '{$row['email']}' existe déjà";
                    $this->skippedCount++;
                    continue;
                }

                // Find country by name
                $country = null;
                if (!empty($row['pays'])) {
                    $country = Country::where('name', 'like', '%' . trim($row['pays']) . '%')->first();
                    if (!$country && $row['pays']) {
                        $this->validationErrors[] = "Ligne $rowNumber: Pays '{$row['pays']}' non trouvé. Utilisé NULL.";
                    }
                }

                // Process the row
                $user = User::where('email', $row['email'])->where('role', 'professional')->first();

                if ($user) {
                    // Update existing user
                    $user->update([
                        'first_name' => $row['prenom'],
                        'last_name' => $row['nom'],
                        'profession' => $this->getProfessionKey($row['profession']),
                        'country_id' => $country ? $country->id : null,
                        'bio' => !empty($row['biographie']) ? $row['biographie'] : null,
                        'is_verified' => strtolower($row['verifie'] ?? 'non') === 'oui',
                        'locale' => $this->getLocaleKey($row['langue'] ?? 'francais'),
                    ]);

                    // Update password if provided
                    if (!empty($row['mot_de_passe'])) {
                        $user->update([
                            'password' => Hash::make($row['mot_de_passe']),
                        ]);
                    }

                    $this->updatedCount++;
                } else {
                    // Create new user
                    User::create([
                        'first_name' => $row['prenom'],
                        'last_name' => $row['nom'],
                        'email' => $row['email'],
                        'password' => Hash::make($row['mot_de_passe'] ?? 'Password123!'),
                        'profession' => $this->getProfessionKey($row['profession']),
                        'country_id' => $country ? $country->id : null,
                        'bio' => !empty($row['biographie']) ? $row['biographie'] : null,
                        'role' => 'professional',
                        'is_verified' => strtolower($row['verifie'] ?? 'non') === 'oui',
                        'locale' => $this->getLocaleKey($row['langue'] ?? 'francais'),
                        'email_verified_at' => strtolower($row['email_verifie'] ?? 'non') === 'oui' ? now() : null,
                    ]);

                    $this->importedCount++;
                }

            } catch (\Exception $e) {
                $this->errors[] = "Ligne $rowNumber: " . $e->getMessage();
                $this->skippedCount++;
            }
        }
    }

    private function getProfessionKey($profession)
    {
        if (empty($profession)) {
            return 'other';
        }

        $professionsMap = [
            'Réalisateur' => 'realisateur',
            'Producteur' => 'producteur',
            'Assistant Réalisateur' => 'assistant_realisateur',
            'Scripte' => 'scripte',
            'Chargé de production' => 'charge_production',
            'Directeur de la photographie' => 'directeur_photographie',
            'Chef opérateur' => 'chef_operateur',
            'Cadreur' => 'cadreur',
            'Cameraman' => 'cameraman',
            'Assistant opérateur' => 'assistant_operateur',
            '1er assistant caméra' => 'premier_assistant_camera',
            'Étalonneur' => 'etalonneur',
            'Ingénieur du son' => 'ingenieur_son',
            'Chef opérateur son' => 'chef_operateur_son',
            'Perchiste' => 'perchiste',
            'Monteur son' => 'monteur_son',
            'Mixeur' => 'mixeur',
            'Designer sonore' => 'designer_sonore',
            'Monteur image' => 'monteur_image',
            'Monteur effets spéciaux' => 'monteur_effets_speciaux',
            'Motion designer' => 'motion_designer',
            'Scénariste' => 'scenariste',
            'Dialoguiste' => 'dialoguiste',
            'Documentariste' => 'documentariste',
            'Animateur 2D' => 'animateur_2d',
            'Animateur 3D' => 'animateur_3d',
            'Storyboarder' => 'storyboarder',
            'Concept artist' => 'concept_artist',
            'Journaliste reporter d\'images' => 'journaliste_reporter_images',
            'Régisseur général' => 'regisseur_general',
            'Technicien broadcasting' => 'technicien_broadcasting',
            'Vidéoaste' => 'videaste',
            'Technicien plateau' => 'technicien_plateau',
            'Opérateur lumière' => 'operateur_lumiere',
            'Créateur de contenu vidéo' => 'createur_contenu_video',
            'Spécialiste réalité virtuelle' => 'specialiste_realite_virtuelle',
            'Spécialiste réalité augmentée' => 'specialiste_realite_augmentee',
            'Responsable de chaîne' => 'responsable_chaine',
            'Community manager vidéo' => 'community_manager_video',
            'Autre' => 'other',
        ];

        // Trim and clean the profession string
        $profession = trim($profession);

        // Try exact match first
        if (isset($professionsMap[$profession])) {
            return $professionsMap[$profession];
        }

        // Try case-insensitive match
        foreach ($professionsMap as $key => $value) {
            if (strtolower($key) === strtolower($profession)) {
                return $value;
            }
        }

        // Try to match without accents
        $professionNoAccents = $this->removeAccents($profession);
        foreach ($professionsMap as $key => $value) {
            if (strtolower($this->removeAccents($key)) === strtolower($professionNoAccents)) {
                return $value;
            }
        }

        // Return 'other' as default
        return 'other';
    }

    private function removeAccents($string)
    {
        $search = ['À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ŋ', 'ŋ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ'];
        $replace = ['A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'N', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o'];

        return str_replace($search, $replace, $string);
    }

    private function getLocaleKey($locale)
    {
        if (empty($locale)) {
            return 'fr';
        }

        $localeMap = [
            'francais' => 'fr',
            'french' => 'fr',
            'français' => 'fr',
            'anglais' => 'en',
            'english' => 'en',
            'fr' => 'fr',
            'en' => 'en',
        ];

        $locale = strtolower(trim($locale));

        return $localeMap[$locale] ?? 'fr';
    }

    public function getErrors()
    {
        return array_merge($this->errors, $this->validationErrors);
    }

    public function getImportedCount()
    {
        return $this->importedCount;
    }

    public function getUpdatedCount()
    {
        return $this->updatedCount;
    }

    public function getSkippedCount()
    {
        return $this->skippedCount;
    }
}
