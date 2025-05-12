<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;

class CountryController extends Controller
{
    public function index()
    {
        $countries = Country::orderBy('name')->get();

        return response()->json($countries);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:countries,name',
        ]);

        $country = Country::create([
            'name' => $request->name,
        ]);

        return response()->json([
            'message' => 'Pays créé avec succès.',
            'country' => $country,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $country = Country::findOrFail($id);

        $request->validate([
            'name' => 'required|string|unique:countries,name,' . $country->id,
        ]);

        $country->update([
            'name' => $request->name,
        ]);

        return response()->json([
            'message' => 'Pays mis à jour avec succès.',
            'country' => $country,
        ]);
    }

    public function destroy($id)
    {
        $country = Country::findOrFail($id);
        $country->delete();

        return response()->json([
            'message' => 'Pays supprimé avec succès.'
        ]);
    }
}
