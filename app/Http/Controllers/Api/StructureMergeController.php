<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StructureMerge;
use App\Models\TemporaryStructure;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class StructureMergeController extends Controller
{
    public function verify(Request $request, $tempStructureId)
    {
        $admin = Auth::user();

        if ($admin->role !== 'admin') {
            return response()->json(['error' => 'Non autorisé.'], 403);
        }

        $temp = TemporaryStructure::findOrFail($tempStructureId);

        if ($temp->is_verified) {
            return response()->json(['message' => 'Structure déjà validée.']);
        }

        // Créer une structure officielle dans users
        $structure = User::create([
            'first_name' => null,
            'last_name' => null,
            'company_name' => $temp->company_name,
            'email' => $request->input('email'), // unique obligatoire
            'password' => Hash::make($request->input('password')),
            'role' => 'structure',
            'sector' => $temp->sector,
            'country_id' => $temp->country_id,
            'website' => $temp->website,
            'is_verified' => true,
            'verified_at' => now(),
            'verified_by' => $admin->id,
        ]);

        // Marquer la structure temporaire comme validée
        $temp->update([
            'is_verified' => true,
            'verified_at' => now(),
            'verified_by' => $admin->id,
        ]);

        // Fusion
        StructureMerge::create([
            'original_id' => $temp->id,
            'official_id' => $structure->id,
            'merged_by' => $admin->id,
            'merged_at' => now(),
            'notes' => $request->input('notes'),
        ]);

        // Update related experiences
    \DB::table('experiences')
    ->where('temp_structure_id', $temp->id)
    ->update(['structure_id' => $structure->id]);


        return response()->json([
            'message' => 'Structure temporaire validée et fusionnée.',
            'structure' => $structure
        ]);
    }
}
