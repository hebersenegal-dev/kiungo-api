<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeMail;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in(['professional', 'structure'])],
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'country_id' => 'required|exists:countries,id',

            // professional fields
            'first_name' => 'required_if:role,professional',
            'last_name' => 'required_if:role,professional',
            'bio' => 'nullable|string',

            // structure fields
            'company_name' => 'required_if:role,structure',
            'sector' => [
                'required_if:role,structure',
                Rule::in([
                    'societe_de_production',
                    'salle_de_cinema',
                    'centre_culturel',
                    'bureau_accueil_tournages',
                    'direction_cinematographie',
                    'fonds_de_soutien',
                    'diffuseur_chaine_tv_plateforme',
                    'association_professionnels',
                    'festival',
                    'institution_publique',
                    'ecole_de_cinema'
                ])
            ],
            'profession' => [
                'required_if:role,professional',
                Rule::in([
                  'realisateur',
                  'producteur',
                  'assistant_realisateur',
                  'scripte',
                  'charge_production',
                  'directeur_photographie',
                  'chef_operateur',
                  'cadreur',
                  'cameraman',
                  'assistant_operateur',
                  'premier_assistant_camera',
                  'etalonneur',
                  'ingenieur_son',
                  'chef_operateur_son',
                  'perchiste',
                  'monteur_son',
                  'mixeur',
                  'designer_sonore',
                  'monteur_image',
                  'monteur_effets_speciaux',
                  'motion_designer',
                  'scenariste',
                  'dialoguiste',
                  'documentariste',
                  'animateur_2d',
                  'animateur_3d',
                  'storyboarder',
                  'concept_artist',
                  'journaliste_reporter_images',
                  'regisseur_general',
                  'technicien_broadcasting',
                  'videaste',
                  'technicien_plateau',
                  'operateur_lumiere',
                  'createur_contenu_video',
                  'specialiste_realite_virtuelle',
                  'specialiste_realite_augmentee',
                  'responsable_chaine',
                  'community_manager_video'
                ])
            ],
            'website' => 'nullable|url',
            'temporary_structure_id' => 'nullable|exists:temporary_structures,id',

            // optional image
            'profile_picture' => 'nullable|image|max:2048',
        ]);

        $path = null;
        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('profile-pictures', 'public');
        }

        $user = User::create([
            'role' => $validated['role'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'country_id' => $validated['country_id'],
            'first_name' => $validated['first_name'] ?? null,
            'last_name' => $validated['last_name'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'sector' => $validated['sector'] ?? null,
            'website' => $validated['website'] ?? null,
            'bio' => $validated['bio'] ?? null,
            'temporary_structure_id' => $validated['temporary_structure_id'] ?? null,
            'profile_picture' => $path,
            'is_verified' => false,
        ]);


        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Utilisateur enregistré avec succès.',
            'user' => $user
        ], 201);


    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Identifiants invalides.'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie.',
            'token' => $token,
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie.'
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
