<?php
namespace App\Http\Controllers\Api;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Models\Experience;
use App\Models\TemporaryStructure;
use App\Models\TemporaryProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ExperienceController extends Controller
{
  public function index()
{
  $experiences = Experience::with(['structure', 'tempStructure', 'project', 'tempProject'])
      ->get();

  $experiences->transform(function ($exp) {
      // Handle structure resolution
      if (!$exp->structure && $exp->temp_structure_id) {
          $merge = \App\Models\StructureMerge::where('original_id', $exp->temp_structure_id)->first();
          if ($merge) {
              $exp->structure = \App\Models\User::find($merge->official_id);
              unset($exp->tempStructure);

          }
      }

      // Handle project resolution
      if (!$exp->project && $exp->temp_project_id) {
          $merge = \App\Models\ProjectMerge::where('original_id', $exp->temp_project_id)->first();
          if ($merge) {
              $exp->project = \App\Models\Project::find($merge->official_id);
              unset($exp->tempProject);
          }
      }

      return $exp;
  });

  return response()->json($experiences);
}


public function show($id)
{
  $experience = Experience::with(['structure', 'tempStructure', 'project', 'tempProject'])
      ->findOrFail($id);

  // Resolve merged structure
  if (!$experience->structure && $experience->temp_structure_id) {
      $merge = \App\Models\StructureMerge::where('original_id', $experience->temp_structure_id)->first();
      if ($merge) {
          $experience->structure = \App\Models\User::find($merge->official_id);
          unset($experience->tempStructure); // Optional: hide the temp version
      }
  }

  // Resolve merged project
  if (!$experience->project && $experience->temp_project_id) {
      $merge = \App\Models\ProjectMerge::where('original_id', $experience->temp_project_id)->first();
      if ($merge) {
          $experience->project = \App\Models\Project::find($merge->official_id);
          unset($experience->tempProject); // Optional: hide the temp version
      }
  }

  return response()->json($experience);
}


    public function store(Request $request)
    {
        $validated = $request->validate([
            'structure_id' => 'nullable|exists:users,id',
            'temp_structure_id' => 'nullable|exists:temporary_structures,id',
            'project_id' => 'nullable|exists:projects,id',
            'temp_project_id' => 'nullable|exists:temporary_projects,id',
            'title' => 'required|string',
            'role' => ['required', 'string', Rule::in(Experience::ROLES)],
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'project.website' => 'nullable|url',
            'project.project_image' => 'nullable|file|image|max:2048',

        ]);

        $experience = Experience::create([
            'user_id' => Auth::id(),
            'structure_id' => $validated['structure_id'] ?? null,
            'temp_structure_id' => $validated['temp_structure_id'] ?? null,
            'project_id' => $validated['project_id'] ?? null,
            'temp_project_id' => $validated['temp_project_id'] ?? null,
            'title' => $validated['title'],
            'role' => $validated['role'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
        ]);

        return response()->json([
            'message' => 'Expérience ajoutée avec succès.',
            'experience' => $experience
        ], 201);
    }

    public function storeWithTemporaries(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'role' => ['required', 'string', Rule::in(Experience::ROLES)],
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',

            'structure_id' => 'nullable|exists:users,id',
            'structure.name' => 'required_without:structure_id|string',
            'structure.sector' => 'required_without:structure_id|string',
            'structure.country_id' => 'required_without:structure_id|exists:countries,id',
            'structure.website' => 'nullable|url',

            'project.title' => 'required|string',
            'project.description' => 'nullable|string',
            'project.country_id' => 'required|exists:countries,id',
            'project.start_date' => 'required|date',
            'project.end_date' => 'required|date|after_or_equal:project.start_date',
            'project.website' => 'nullable|url',
        ]);

        $structureId = $validated['structure_id'] ?? null;
        $structureData = $validated['structure'] ?? null;
        $projectData = $validated['project'];

        // Either use existing structure or create temp one
        if ($structureId) {
            $structure = User::where('role', 'structure')->findOrFail($structureId);
            $tempStructure = null;
        } else {
            $tempStructure = TemporaryStructure::create([
                'company_name' => $structureData['name'],
                'sector' => $structureData['sector'],
                'country_id' => $structureData['country_id'],
                'website' => $structureData['website'] ?? null,
                'added_by' => Auth::id(),
                'is_verified' => false,
            ]);
            $structureId = null;
        }

        $tempProject = TemporaryProject::create([
            'title' => $projectData['title'],
            'description' => $projectData['description'] ?? null,
            'country_id' => $projectData['country_id'],
            'temp_structure_id' => $tempStructure->id ?? null,
            'structure_id' => $structureId,
            'start_date' => $projectData['start_date'],
            'end_date' => $projectData['end_date'],
            'website' => $projectData['website'] ?? null,
            'project_image' => $request->file('project.project_image')?->store('project_images'),
            'added_by' => Auth::id(),
            'is_verified' => false,
        ]);

        $experience = Experience::create([
            'user_id' => Auth::id(),
            'structure_id' => $structureId,
            'temp_structure_id' => $tempStructure->id ?? null,
            'temp_project_id' => $tempProject->id,
            'title' => $validated['title'],
            'role' => $validated['role'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
        ]);

        return response()->json([
            'message' => 'Expérience enregistrée avec projet temporaire et structure ' . ($structureId ? 'officielle' : 'temporaire') . '.',
            'experience' => $experience,
            'temporary_structure' => $tempStructure,
            'temporary_project' => $tempProject,
        ], 201);
    }



    public function update(Request $request, $id)
    {
        $experience = Experience::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'structure_id' => 'nullable|exists:users,id',
            'temp_structure_id' => 'nullable|exists:temporary_structures,id',
            'project_id' => 'nullable|exists:projects,id',
            'temp_project_id' => 'nullable|exists:temporary_projects,id',
            'title' => 'required|string',
            'role' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $experience->update($validated);

        return response()->json([
            'message' => 'Expérience mise à jour.',
            'experience' => $experience
        ]);
    }

    public function destroy($id)
    {
        $experience = Experience::where('user_id', Auth::id())->findOrFail($id);
        $experience->delete();

        return response()->json(['message' => 'Expérience supprimée.']);
    }

    public function myExperiences()
{
    $user = Auth::user();

    if ($user->role !== 'professional') {
        return response()->json(['error' => 'Non autorisé.'], 403);
    }

    $experiences = Experience::where('user_id', $user->id)
        ->with(['structure', 'tempStructure', 'project', 'tempProject'])
        ->get();

    // Apply resolution logic for merged data
    $experiences->transform(function ($exp) {
        if (!$exp->structure && $exp->temp_structure_id) {
            $merge = \App\Models\StructureMerge::where('original_id', $exp->temp_structure_id)->first();
            if ($merge) {
                $exp->structure = \App\Models\User::find($merge->official_id);
                unset($exp->tempStructure);
            }
        }

        if (!$exp->project && $exp->temp_project_id) {
            $merge = \App\Models\ProjectMerge::where('original_id', $exp->temp_project_id)->first();
            if ($merge) {
                $exp->project = \App\Models\Project::find($merge->official_id);
                unset($exp->tempProject);
            }
        }

        return $exp;
    });

    return response()->json($experiences);
}

}
