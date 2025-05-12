<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class StructureController extends Controller
{
    public function index()
    {
        $structures = User::where('role', 'structure')
                          ->orderBy('company_name')
                          ->get();

        return response()->json($structures);
    }

    public function show($id)
    {
        $structure = User::where('role', 'structure')->findOrFail($id);

        return response()->json($structure);
    }

    public function update(Request $request, $id)
    {
        $structure = User::where('role', 'structure')->findOrFail($id);

        // Optionnel : autoriser uniquement la structure elle-même ou un admin
        if ($request->user()->id !== $structure->id && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $request->validate([
            'company_name' => 'required|string|max:255',
            'sector' => 'required|string|max:255',
            'website' => 'nullable|url',
            'country_id' => 'required|exists:countries,id',
        ]);

        $structure->update($request->only('company_name', 'sector', 'website', 'country_id'));

        return response()->json([
            'message' => 'Structure mise à jour avec succès.',
            'structure' => $structure,
        ]);
    }

    public function destroy($id)
    {
        $structure = User::where('role', 'structure')->findOrFail($id);

        // Optionnel : uniquement pour un admin
        $structure->delete();

        return response()->json([
            'message' => 'Structure supprimée avec succès.'
        ]);
    }
}
