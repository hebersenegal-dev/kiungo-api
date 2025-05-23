<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TemporaryStructure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TemporaryStructureController extends Controller
{
    // Liste toutes les structures temporaires
    public function index()
    {
        return TemporaryStructure::with('country')->get();
    }

    // Ajout d’une structure temporaire
    public function store(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'sector' => 'required|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'website' => 'nullable|url',
        ]);

        $structure = TemporaryStructure::create([
            'company_name' => $request->input('company_name'),
            'sector' => $request->input('sector'),
            'country_id' => $request->input('country_id'),
            'website' => $request->input('website'),
            'added_by' => Auth::id(),
            'is_verified' => false,
        ]);

        return response()->json([
            'message' => 'Structure temporaire ajoutée.',
            'structure' => $structure
        ], 201);
    }

    // Détails d’une structure temporaire
    public function show($id)
    {
        $structure = TemporaryStructure::with('country')->findOrFail($id);
        return response()->json($structure);
    }

    // Mise à jour d’une structure temporaire
public function update(Request $request, $id)
{
    $structure = TemporaryStructure::findOrFail($id);

    // Vérifie que l'utilisateur connecté est bien celui qui a ajouté la structure
    if ($structure->added_by !== Auth::id()) {
        return response()->json(['message' => 'Non autorisé à modifier cette structure.'], 403);
    }

    $request->validate([
        'company_name' => 'sometimes|required|string|max:255',
        'sector' => 'sometimes|required|string|max:255',
        'country_id' => 'sometimes|required|exists:countries,id',
        'website' => 'nullable|url',
    ]);

    $structure->update($request->only(['company_name', 'sector', 'country_id', 'website']));

    return response()->json([
        'message' => 'Structure temporaire mise à jour.',
        'structure' => $structure
    ]);
}

}
