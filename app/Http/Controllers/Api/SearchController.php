<?php

namespace App\Http\Controllers\Api;
use App\Models\Experience;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'profession' => ['nullable', 'string', Rule::in(Experience::ROLES)],
            'structure' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255'
        ]);

        $query = User::where('role', 'professional')
            ->with([
                'experiences.structure',
                'experiences.tempStructure',
                'country'
            ]);

        // Full name search (first_name + last_name)
        if ($request->has('name')) {
            $query->where(function($q) use ($request) {
                $q->where('first_name', 'like', '%'.$request->name.'%')
                  ->orWhere('last_name', 'like', '%'.$request->name.'%');
            });
        }

        // Profession filter using your ROLES enum
        if ($request->has('profession')) {
            $query->whereHas('experiences', function($q) use ($request) {
                $q->where('role', $request->profession);
            });
        }

        // Structure search (both official and temporary)
        if ($request->has('structure')) {
            $query->whereHas('experiences', function($q) use ($request) {
                $q->whereHas('structure', function($q) use ($request) {
                    $q->where('company_name', 'like', '%'.$request->structure.'%');
                })->orWhereHas('tempStructure', function($q) use ($request) {
                    $q->where('company_name', 'like', '%'.$request->structure.'%');
                });
            });
        }

        // Country filter
        if ($request->has('country')) {
            $query->whereHas('country', function($q) use ($request) {
                $q->where('name', 'like', '%'.$request->country.'%');
            });
        }

        return response()->json([
            'professionals' => $query->paginate(15),
            'filters' => $validated
        ]);
    }

    public function autocompleteName(Request $request)
{
    $term = $request->get('query');

    $query = User::where('role', 'professional');


    if ($term) {
        $query->where(function ($q) use ($term) {
            $q->where('first_name', 'like', "%$term%")
              ->orWhere('last_name', 'like', "%$term%")
              ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$term%"]);
        });
    }


    if ($request->has('country')) {
        $query->whereHas('country', function ($q) use ($request) {
            $q->where('name', 'like', '%'.$request->country.'%');
        });
    }


    if ($request->has('profession')) {
        $query->whereHas('experiences', function ($q) use ($request) {
            $q->where('role', $request->profession);
        });
    }


    if ($request->has('structure')) {
        $query->whereHas('experiences', function ($q) use ($request) {
            $q->whereHas('structure', function ($q) use ($request) {
                $q->where('company_name', 'like', '%'.$request->structure.'%');
            })->orWhereHas('tempStructure', function ($q) use ($request) {
                $q->where('company_name', 'like', '%'.$request->structure.'%');
            });
        });
    }

    $results = $query->limit(10)->get()->map(function ($user) {
      return [
           'name' => $user->first_name . ' ' . $user->last_name,
           'profile_picture' => $user->profile_picture ? asset('storage/' . $user->profile_picture) : null,
           'user_id'=> $user->id,
       ];

    })->unique()->values();

    return response()->json($results);
}



}
