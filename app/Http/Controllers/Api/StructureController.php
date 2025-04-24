<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Structure;
use Illuminate\Http\Request;

class StructureController extends Controller
{
    public function index()
    {
        return Structure::with('country')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'logo' => 'nullable|string',
            'website' => 'nullable|url',
            'country_id' => 'required|exists:countries,id',
        ]);

        $structure = Structure::create($validated);

        return response()->json($structure, 201);
    }

    public function show(Structure $structure)
    {
        return $structure->load('country');
    }

    public function update(Request $request, Structure $structure)
    {
        $structure->update($request->only(['name', 'description', 'logo', 'website', 'country_id']));

        return response()->json($structure);
    }

    public function destroy(Structure $structure)
    {
        $structure->delete();

        return response()->json(null, 204);
    }
}
