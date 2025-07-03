<?php

namespace App\Http\Controllers\Api;

use App\Models\Experience;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'profession' => 'nullable',
            'structure' => 'nullable',
            'country' => 'nullable|string|max:255'
        ]);

        $query = User::where('role', 'professional')
            ->with([
                'experiences.structure',
                'experiences.tempStructure',
                'country'
            ]);

        // Full name search
        if ($request->filled('name')) {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->name . '%')
                    ->orWhere('last_name', 'like', '%' . $request->name . '%');
            });
        }

        // Multiple professions (OR condition)
        $professions = $request->get('profession');
        if ($professions) {
            $professionsArray = is_array($professions) ? $professions : explode(',', $professions);
            $query->whereIn('profession', $professionsArray);
        }

        // Multiple structures (OR condition)
        $structures = $request->get('structure');
        if ($structures) {
            $structuresArray = is_array($structures) ? $structures : explode(',', $structures);
            $query->whereHas('experiences', function ($q) use ($structuresArray) {
                $q->where(function ($q2) use ($structuresArray) {
                    foreach ($structuresArray as $structure) {
                        $q2->orWhereHas('structure', function ($sq) use ($structure) {
                            $sq->where('company_name', 'like', '%' . $structure . '%');
                        })->orWhereHas('tempStructure', function ($sq) use ($structure) {
                            $sq->where('company_name', 'like', '%' . $structure . '%');
                        });
                    }
                });
            });
        }

        // Country filter
        if ($request->has('country')) {
            $query->whereHas('country', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->country . '%');
            });
        }

        return response()->json([
            'professionals' => $query->paginate(15),
            'filters' => $request->all()
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

        // Country filter
        if ($request->has('country')) {
            $query->whereHas('country', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->country . '%');
            });
        }

        // Multiple professions (OR)
        $professions = $request->get('profession');
        if ($professions) {
            $professionsArray = is_array($professions) ? $professions : explode(',', $professions);
            $query->whereIn('profession', $professionsArray);
        }

        // Multiple structures (OR)
        $structures = $request->get('structure');
        if ($structures) {
            $structuresArray = is_array($structures) ? $structures : explode(',', $structures);
            $query->whereHas('experiences', function ($q) use ($structuresArray) {
                $q->where(function ($q2) use ($structuresArray) {
                    foreach ($structuresArray as $structure) {
                        $q2->orWhereHas('structure', function ($sq) use ($structure) {
                            $sq->where('company_name', 'like', '%' . $structure . '%');
                        })->orWhereHas('tempStructure', function ($sq) use ($structure) {
                            $sq->where('company_name', 'like', '%' . $structure . '%');
                        });
                    }
                });
            });
        }

        $results = $query->limit(10)->get()->map(function ($user) {
            return [
                'name' => $user->first_name . ' ' . $user->last_name,
                'profile_picture' => $user->profile_picture ? asset('storage/' . $user->profile_picture) : null,
                'user_id' => $user->id,
            ];
        })->unique()->values();

        return response()->json($results);
    }
}
