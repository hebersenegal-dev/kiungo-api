<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TemporaryProject;
use App\Notifications\TempProjectRejected;
use App\Models\Project;
use App\Models\ProjectMerge;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class ProjectMergeController extends Controller
{
  public function verify(Request $request, $tempProjectId)
{
    $user = Auth::user();

    if (!in_array($user->role, ['admin', 'structure'])) {
        return response()->json(['error' => 'Non autorisé.'], 403);
    }

    $temp = TemporaryProject::findOrFail($tempProjectId);

    if ($temp->is_verified) {
        return response()->json(['message' => 'Projet déjà validé.']);
    }

    // Check that at least one structure reference exists
    if (!$temp->structure_id && !$temp->temp_structure_id) {
        return response()->json(['error' => 'Aucune structure liée au projet.'], 400);
    }

    $structureId = null;

    // ✅ Case 1: The project is linked to a temporary structure
    if ($temp->temp_structure_id) {
        $merge = \App\Models\StructureMerge::where('original_id', $temp->temp_structure_id)->first();

        if (!$merge) {
            return response()->json(['error' => 'La structure liée n’a pas encore été fusionnée.'], 400);
        }

        $structureId = $merge->official_id;
    }

    // ✅ Case 2: The project is linked directly to an existing structure
    if (!$structureId && $temp->structure_id) {
        $structureId = $temp->structure_id;
    }

    // ✅ Final safety check
    if (!$structureId) {
        return response()->json(['error' => 'Impossible de déterminer la structure du projet.'], 400);
    }

    // Create the official project
    $project = Project::create([
        'title' => $temp->title,
        'description' => $temp->description,
        'start_date' => $temp->start_date,
        'end_date' => $temp->end_date,
        'structure_id' => $structureId,
        'created_by' => $user->id,
        'website' => $temp->website,
        'project_image' => $temp->project_image,
        'is_verified' => true,
        'is_temp_created' => true,
        'approved_by' => $user->id,
        'approved_at' => now(),
    ]);

    // Update temporary project status
    $temp->update([
        'is_verified' => true,
        'verified_at' => now(),
        'verified_by' => $user->id,
    ]);

    // Register the merge link if temp_structure_id exists
    if ($temp->temp_structure_id) {
        ProjectMerge::create([
            'original_id' => $temp->id,
            'official_id' => $project->id,
            'merged_by' => $user->id,
            'merged_at' => now(),
            'notes' => $request->input('notes'),
        ]);
    }

    // Update related experiences
    \DB::table('experiences')
        ->where('temp_project_id', $temp->id)
        ->update(['project_id' => $project->id]);

    return response()->json([
        'message' => 'Projet temporaire validé et fusionné avec succès.',
        'project' => $project
    ]);
}


    public function reject(Request $request, $tempProjectId)
    {
        $user = Auth::user();

        // Only admin or structure can reject a project
        if (!in_array($user->role, ['admin', 'structure'])) {
            return response()->json(['error' => 'Non autorisé.'], 403);
        }

        // Validate rejection reason
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        // Find the temporary project
        $temp = TemporaryProject::findOrFail($tempProjectId);
        $creator = User::find($temp->added_by); // ✅ changed here

        // Save the rejection reason
        $reason = $validated['reason'];

        // Delete the temporary project
        $temp->delete();

        // Notify the creator if exists
        if ($creator) {
            $creator->notify(new \App\Notifications\TempProjectRejected($temp, $reason));
        }

        return response()->json([
            'message' => 'Le projet temporaire a été rejeté et supprimé avec succès.',
            'notified_user' => $creator ? $creator->email : null, // ✅ optional, for info
        ]);
    }

}
