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
        return response()->json(
            TemporaryProject::where('added_by', Auth::id())->with('tempStructure')->get()
        );
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

    // Fetch all temporary projects where temp_structure_id matches the structure (user) ID
    $projects = TemporaryProject::where('temp_structure_id', $user->id)
        ->with('tempStructure')
        ->get();

    return response()->json($projects);
}




}
