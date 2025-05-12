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

    public function show($id)
    {
        $user = User::with('country')->findOrFail($id);
        return response()->json($user);
    }
    public function updateMe(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'company_name' => 'nullable|string',
            'sector' => 'nullable|string',
            'website' => 'nullable|url',
            'bio' => 'nullable|string',
            'country_id' => 'nullable|exists:countries,id',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('profile_pictures', $filename, 'public');
            $validated['profile_picture'] = $path;
        }

        $user->update($validated);

      
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

}
