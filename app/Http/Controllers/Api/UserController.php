<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;



class UserController extends Controller
{
    public function index()
    {

        return response()->json(User::all());
    }

    public function getProfessionals()
{
    $professionals = User::where('role', 'professional')
        ->with('country:id,name')
        ->get(['first_name', 'last_name', 'country_id', 'profession', 'profile_picture', 'id', 'is_verified']);

    // Transform the result to include country name
    $transformed = $professionals->map(function ($user) {
        return [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'id'=> $user->id,
            'profession' => $user->profession,
            'profile_picture' => $user->profile_picture_url,
            'country' => $user->country?->name,
            'is_verified' => $user->is_verified,
        ];
    });

    return response()->json($transformed);
}



    public function show($id)
{
    $user = User::with([
        'country',
        'awards',
        'experiences' => function($query) {
            $query->with([
                'structure:id,company_name,sector,country_id,website,profile_picture',
                'tempStructure:id,company_name,sector,country_id,website',
                'project:id,title,description,start_date,end_date,website,project_image,is_verified',
                'tempProject:id,title,description,start_date,end_date,website,project_image'
            ])
            ->orderByDesc('start_date')
            ->orderByDesc('end_date');
        }
    ])->findOrFail($id);

    // Transform the experiences data for cleaner output
    $userData = $user->toArray();

    if (isset($userData['experiences'])) {
        $userData['experiences'] = array_map(function($experience) {
            return [
                'id' => $experience['id'],
                'title' => $experience['title'],
                'role' => $experience['role'],
                'start_date' => $experience['start_date'],
                'end_date' => $experience['end_date'],
                'structure' => $experience['structure'] ?? null,
                'temp_structure' => $experience['temp_structure'] ?? null,
                'project' => $experience['project'] ?? null,
                'temp_project' => $experience['temp_project'] ?? null
            ];
        }, $userData['experiences']);
    }

    return response()->json($userData);
}



    public function updateMe(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        \Log::info('User Authenticated:', ['id' => $user->id]);
        \Log::info('Request Data:', $request->all());

        $validated = $request->validate([
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'company_name' => 'nullable|string',
            'sector' => [
                'nullable',
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
            'bio' => 'nullable|string',
            'country_id' => 'nullable|exists:countries,id',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
            'social_medias' => 'nullable|array',
            'social_medias.*.platform' => 'required_with:social_medias.*.url|string',
            'social_medias.*.url' => 'required_with:social_medias.*.platform|url',
        ]);

        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('profile_pictures', $filename, 'public');
            $validated['profile_picture'] = $path;

            \Log::info('Stored file path:', ['path' => $path]);
        }



        $success = $user->update($validated);
        \Log::info('Update success?', ['success' => $success]);

        return response()->json([
            'message' => 'Profil mis à jour avec succès.',
            'user' => $user->fresh()
        ]);
    }




    public function verifyUser($id)
    {
        $admin = Auth::user();
        if ($admin->role !== 'admin') {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $user = User::findOrFail($id);
        $user->update([
            'is_verified' => true,
            'verified_at' => now(),
            'verified_by' => $admin->id
        ]);


        // Gestion de la fusion automatique si la structure avait une temporary_structure_id
        if ($user->role === 'structure' && $user->temporary_structure_id) {
            \App\Models\StructureMerge::create([
                'original_id' => $user->temporary_structure_id,
                'official_id' => $user->id,
                'merged_by' => $admin->id,
                'merged_at' => now(),
            ]);
        }

        return response()->json([
            'message' => 'Utilisateur vérifié.',
            'user' => $user
        ]);
    }

    
  public function updateLocale(Request $request)
  {
      $request->validate([
          'locale' => 'required|string|in:en,fr',
      ]);

      $user = Auth::user();

      if (!$user) {
          return response()->json(['error' => 'Unauthenticated'], 401);
      }

      // Update user's locale
      $user->locale = $request->locale;
      $user->save();

      return response()->json([
          'message' => 'Locale updated successfully',
          'locale' => $user->locale,
      ]);
  }

}
