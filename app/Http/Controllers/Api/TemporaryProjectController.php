<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Models\TemporaryProject;
use App\Models\Project;
use App\Models\ProjectMerge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TemporaryProjectController extends Controller
{
  public function index()
{
  $user = Auth::user();


  if ($user->role !== 'structure') {
      return response()->json(['error' => 'Non autorisé.'], 403);
  }

  $projects = TemporaryProject::where('temp_structure_id', $user->id)
      ->where('is_verified', false)
      ->with('tempStructure')
      ->get();

  return response()->json($projects);
}


    public function show($id)
    {
        $project = TemporaryProject::with('tempStructure')->findOrFail($id);
        return response()->json($project);
    }

    public function store(Request $request)
{
    if (Auth::user()->role !== 'professional') {
        return response()->json(['error' => 'Seuls les professionnels peuvent créer des projets temporaires.'], 403);
    }

    $validated = $request->validate([
        'title' => 'required|string',
        'description' => 'required|string',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'temp_structure_id' => 'required|exists:temporary_structures,id',
        'website' => 'nullable|url',
        'project_image' => 'nullable|image|max:2048', // Max 2MB
    ]);

    $imagePath = null;

    if ($request->hasFile('project_image')) {
        $imagePath = $request->file('project_image')->store('project_images', 'public');
    }

    $project = TemporaryProject::create([
        'title' => $validated['title'],
        'description' => $validated['description'],
        'start_date' => $validated['start_date'],
        'end_date' => $validated['end_date'],
        'temp_structure_id' => $validated['temp_structure_id'],
        'website' => $validated['website'] ?? null,
        'project_image' => $imagePath,
        'added_by' => Auth::id(),
        'is_verified' => false,
    ]);

    return response()->json([
        'message' => 'Projet temporaire créé.',
        'project' => $project->load('tempStructure') // include related structure
    ], 201);
}


public function verify(Request $request, $id)
{
  $user = Auth::user();
  if (!in_array($user->role, ['admin', 'structure'])) {
      return response()->json(['error' => 'Non autorisé.'], 403);
  }

  $tempProject = TemporaryProject::findOrFail($id);

  if ($tempProject->is_verified) {
      return response()->json(['message' => 'Déjà validé.']);
  }

  // Créer le projet officiel
  $official = Project::create([
      'title' => $tempProject->title,
      'description' => $tempProject->description,
      'start_date' => $tempProject->start_date,
      'end_date' => $tempProject->end_date,
      'website' => $tempProject->website,
      'structure_id' => $user->role === 'structure' ? $user->id : null,
      'created_by' => $tempProject->added_by,
      'is_verified' => true,
      'is_temp_created' => true,
      'approved_by' => $user->id,
      'approved_at' => now(),
      'project_image' => $tempProject->project_image,
  ]);

  // Mettre à jour le projet temporaire
  $tempProject->update([
      'is_verified' => true,
      'verified_at' => now(),
      'verified_by' => $user->id,
  ]);

  // Fusion
  ProjectMerge::create([
      'original_id' => $tempProject->id,
      'official_id' => $official->id,
      'merged_by' => $user->id,
      'merged_at' => now(),
      'notes' => $request->input('notes') ?? null
  ]);

  return response()->json([
      'message' => 'Projet temporaire validé et fusionné.',
      'official_project' => $official
  ]);
}

public function structureProjects()
{
    $user = Auth::user();

    if ($user->role !== 'structure') {
        return response()->json(['error' => 'Non autorisé.'], 403);
    }

    $projects = TemporaryProject::where('structure_id', $user->id)
        ->with('structure')
        ->get();

    return response()->json($projects);
}


public function update(Request $request, $id)
{
    $project = TemporaryProject::findOrFail($id);

    // Vérifie que l'utilisateur connecté est celui qui a ajouté le projet
    if ($project->added_by !== Auth::id()) {
        return response()->json(['message' => 'Non autorisé à modifier ce projet.'], 403);
    }

    $validated = $request->validate([
        'title' => 'sometimes|required|string',
        'description' => 'sometimes|required|string',
        'start_date' => 'sometimes|required|date',
        'end_date' => 'sometimes|required|date|after_or_equal:start_date',
        'temp_structure_id' => 'nullable|exists:temporary_structures,id',
        'structure_id' => 'nullable|exists:structures,id',
        'website' => 'nullable|url',
        'project_image' => 'nullable|image|max:2048',
    ]);

    // At least one structure ID must be provided if modifying them
    if ($request->has('temp_structure_id') || $request->has('structure_id')) {
        if (!$request->filled('temp_structure_id') && !$request->filled('structure_id')) {
            return response()->json(['error' => 'Vous devez fournir temp_structure_id ou structure_id.'], 422);
        }
    }

    // Handle new image upload if provided
    if ($request->hasFile('project_image')) {
        $validated['project_image'] = $request->file('project_image')->store('project_images', 'public');
    }

    $project->update($validated);

    return response()->json([
        'message' => 'Projet temporaire mis à jour.',
        'project' => $project->load('tempStructure')
    ]);
}




}
