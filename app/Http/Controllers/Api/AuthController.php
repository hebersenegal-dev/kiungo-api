<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
  /**
 * Register a new user (Professional or Structure)
 *
 * @bodyParam role string required Role of the user. Must be either 'professional' or 'structure'. Example: professional
 * @bodyParam email string required The email address of the user. Example: user@example.com
 * @bodyParam password string required The user's password (minimum 8 characters). Example: password123
 * @bodyParam password_confirmation string required Password confirmation. Example: password123
 * @bodyParam country_id integer required The ID of the user's country. Example: 1
 *
 * @bodyParam first_name string required if role is professional The user's first name. Example: John
 * @bodyParam last_name string required if role is professional The user's last name. Example: Doe
 * @bodyParam bio string The user's biography. Example: Full-stack developer with 5 years experience.
 *
 * @bodyParam company_name string required if role is structure The name of the company. Example: TechCorp
 * @bodyParam sector string required if role is structure The business sector. Example: IT
 * @bodyParam website string The company's website. Example: https://techcorp.com
 * @bodyParam temporary_structure_id integer The ID of a temporary structure. Example: 2
 *
 * @bodyParam profile_picture file An optional profile image file (max 2MB).
 *
 * @response 201 {
 *   "message": "Utilisateur enregistré avec succès.",
 *   "user": {
 *     "id": 1,
 *     "email": "user@example.com",
 *     "role": "professional",
 *     "first_name": "John",
 *     "last_name": "Doe",
 *     ...
 *   }
 * }
 */

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
      'sector' => 'required_if:role,structure',
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
