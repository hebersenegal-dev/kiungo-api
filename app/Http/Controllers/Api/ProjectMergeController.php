<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TemporaryProject;
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

        if (!$temp->temp_structure_id && !$temp->structure_id ) {
            return response()->json(['error' => 'Aucune structure liée au projet.'], 400);
        }

        $merge = \App\Models\StructureMerge::where('original_id', $temp->temp_structure_id)->first();

        if (!$merge) {
            return response()->json(['error' => 'La structure liée n’a pas encore été fusionnée.'], 400);
        }

        // Création du projet officiel
        $project = Project::create([
            'title' => $temp->title,
            'description' => $temp->description,
            'start_date' => $temp->start_date,
            'end_date' => $temp->end_date,
            'structure_id' => $merge->official_id,
            'created_by' => $user->id,
            'website' => $temp->website,
            'project_image' => $temp->project_image,
            'is_verified' => true,
            'is_temp_created' => true,
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        // Mise à jour du projet temporaire
        $temp->update([
            'is_verified' => true,
            'verified_at' => now(),
            'verified_by' => $user->id,
        ]);

        // Enregistrement de la fusion
        ProjectMerge::create([
            'original_id' => $temp->id,
            'official_id' => $project->id,
            'merged_by' => $user->id,
            'merged_at' => now(),
            'notes' => $request->input('notes'),
        ]);


        \DB::table('experiences')
        ->where('temp_project_id', $temp->id)
        ->update(['project_id' => $project->id]);


        return response()->json([
            'message' => 'Projet temporaire validé et fusionné.',
            'project' => $project
        ]);
    }
}
