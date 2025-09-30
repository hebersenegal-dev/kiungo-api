<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Experience;
use App\Models\User;
use App\Models\ProjectMerge;
use App\Models\TemporaryProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{

    public function index()
    {
        $query = Project::query()->with('structure');

        if (Auth::user()->role === 'structure') {
            $query->where('structure_id', Auth::id());
        }

        return response()->json($query->get());
    }

    public function getProjectsByStructure($structureId)
{

    $projects = Project::with('structure')
        ->where('structure_id', $structureId)
        ->get();

    return response()->json($projects);
}


    public function show($id)
    {
        $project = Project::with('structure')->findOrFail($id);
        return response()->json($project);
    }

    public function update(Request $request, $id)
  {
      $project = Project::findOrFail($id);

      if ($project->structure_id !== Auth::id()) {
          return response()->json(['error' => 'Non autorisé.'], 403);
      }

      $validated = $request->validate([
          'title' => 'sometimes|string|max:255',
          'description' => 'sometimes|string',
          'start_date' => 'sometimes|date',
          'end_date' => 'sometimes|date|after_or_equal:start_date',
          'website' => 'nullable|url',
          'project_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
      ]);

      if ($request->hasFile('project_image')) {
          if ($project->project_image && \Storage::disk('public')->exists($project->project_image)) {
              \Storage::disk('public')->delete($project->project_image);
          }

          $validated['project_image'] = $request->file('project_image')->store('projects', 'public');
      }

      $project->update($validated);

      return response()->json([
          'message' => 'Projet mis à jour.',
          'project' => $project
      ]);
  }


    public function verify($id)
    {
        $user = Auth::user();
        $project = Project::findOrFail($id);

        if (!in_array($user->role, ['admin', 'structure'])) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $project->update([
            'is_verified' => true,
            'approved_by' => $user->id,
            'approved_at' => now()
        ]);

        // Si c'est une fusion depuis un projet temporaire (à venir)
        if ($project->is_temp_created) {
            ProjectMerge::create([
                'original_id' => $request->input('temp_project_id'),
                'official_id' => $project->id,
                'merged_by' => $user->id,
                'merged_at' => now(),
                'notes' => $request->input('notes') ?? null
            ]);
        }

        return response()->json([
            'message' => 'Projet validé.',
            'project' => $project
        ]);
    }

    public function store(Request $request)
{
    $user = Auth::user();

    // Only users with the 'structure' role can create projects
    if ($user->role !== 'structure') {
        return response()->json(['error' => 'Non autorisé.'], 403);
    }

    // Validate input
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'start_date' => 'required|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
        'website' => 'nullable|url',
        'project_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
    ]);

    // Handle image upload if provided
    if ($request->hasFile('project_image')) {
        $validated['project_image'] = $request->file('project_image')->store('projects', 'public');
    }

    // Associate project with authenticated structure
    $validated['structure_id'] = $user->id;
    $validated['created_by'] = $user->id;
    $validated['is_temp_created'] = false;
    $validated['is_verified'] = false;

    $project = Project::create($validated);

    return response()->json([
        'message' => 'Projet créé avec succès.',
        'project' => $project
    ], 201);
}

public function destroy($id)
{
    $user = Auth::user();
    $project = Project::findOrFail($id);

    // Only the owning structure can delete their project
    if ($user->role !== 'structure' || $project->structure_id !== $user->id) {
        return response()->json(['error' => 'Non autorisé.'], 403);
    }

    // Delete project image if exists
    if ($project->project_image && \Storage::disk('public')->exists($project->project_image)) {
        \Storage::disk('public')->delete($project->project_image);
    }

    // Delete the project
    $project->delete();

    return response()->json(['message' => 'Projet supprimé.']);
}

public function myProjects()
{
    $user = Auth::user();

    if ($user->role !== 'structure') {
        return response()->json(['error' => 'Non autorisé.'], 403);
    }

    $projects = Project::with('structure')
        ->where('structure_id', $user->id)
        ->get();

    return response()->json($projects);
}

public function getProjectProfessionals($projectId)
{
    \Log::info('getProjectProfessionals called with projectId: ' . $projectId);

    try {
        // Check if project exists
        $project = Project::find($projectId);

        if (!$project) {
            \Log::error('Project not found: ' . $projectId);
            return response()->json(['error' => 'Project not found'], 404);
        }

        \Log::info('Project found: ' . $project->title);

        // Get experiences for this project (both regular and temp projects)
        $experiences = Experience::where(function($query) use ($projectId) {
            $query->where('project_id', $projectId)
                  ->orWhere('temp_project_id', $projectId);
        })
        ->with(['user' => function($query) {
            $query->select('id', 'first_name', 'last_name', 'profession', 'role')
                  ->where('role', 'professional');
        }])
        ->get();

        \Log::info('Experiences found: ' . $experiences->count());

        $professionals = $experiences->map(function($experience) {
            if (!$experience->user) {
                \Log::warning('Experience found without user: ' . $experience->id);
                return null;
            }

            return [
                'id' => $experience->user->id,
                'first_name' => $experience->user->first_name,
                'last_name' => $experience->user->last_name,
                'profession' => $experience->user->profession,
                'role_on_project' => $experience->role,
            ];
        })->filter(); // Remove null values

        \Log::info('Professionals processed: ' . $professionals->count());

        return response()->json($professionals->values());

    } catch (\Exception $e) {
        \Log::error('Error in getProjectProfessionals: ' . $e->getMessage());
        \Log::error($e->getTraceAsString());

        return response()->json([
            'error' => 'Failed to fetch professionals',
            'message' => $e->getMessage()
        ], 500);
    }
}



}
