<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Experience;
use Illuminate\Http\Request;

class ExperienceController extends Controller
{
  public function index()
{
  return Experience::with(['user', 'professionals', 'structures'])->get();
}


    public function store(Request $request)
  {
      $validated = $request->validate([
          'title' => 'required|string',
          'role' => 'required|string',
          'project_link' => 'nullable|url',
          'start_year' => 'required|digits:4|integer',
          'end_year' => 'nullable|digits:4|integer',
          'professional_ids' => 'nullable|array',
          'professional_ids.*' => 'exists:users,id',
          'structure_ids' => 'nullable|array',
          'structure_ids.*' => 'exists:structures,id',
      ]);

      $experience = $request->user()->experiences()->create($validated);

      if ($request->has('professional_ids')) {
          $experience->professionals()->attach($request->input('professional_ids'));
      }

      if ($request->has('structure_ids')) {
          $experience->structures()->attach($request->input('structure_ids'));
      }

      return response()->json(
          $experience->load('user', 'professionals', 'structures'),
          201
      );
  }


    public function show(Experience $experience)
    {
        return $experience->load('user');
    }

    public function update(Request $request, Experience $experience)
    {
        $this->authorize('update', $experience);

        $experience->update($request->only(['title', 'role', 'project_link', 'start_year', 'end_year']));

        return response()->json($experience);
    }

    public function destroy(Experience $experience)
    {
        $this->authorize('delete', $experience);
        $experience->delete();

        return response()->json(null, 204);
    }
}
