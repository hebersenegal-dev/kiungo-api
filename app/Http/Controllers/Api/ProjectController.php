<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectMerge;
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
        'title' => 'sometimes|string',
        'description' => 'sometimes|string',
        'start_date' => 'sometimes|date',
        'end_date' => 'sometimes|date|after_or_equal:start_date',
        'website' => 'nullable|url',
        'project_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
    ]);

    if ($request->hasFile('project_image')) {
        // Delete old image if it exists
        if ($project->project_image && \Storage::disk('public')->exists($project->project_image)) {
            \Storage::disk('public')->delete($project->project_image);
        }

        // Store new image
        $validated['project_image'] = $request->file('project_image')->store('projects', 'public');
    }

    $project->update($validated);

    return response()->json([
        'message' => 'Projet mis à jour.',
        'project' => $project
    ]);
}


    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        if ($project->structure_id !== Auth::id()) {
            return response()->json(['error' => 'Non autorisé.'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string',
            'description' => 'sometimes|string',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'website' => 'nullable|url'
        ]);

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
                'original_id' => $request->input('temp_project_id'), // à adapter selon ton workflow
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

}
