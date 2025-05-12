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
}
