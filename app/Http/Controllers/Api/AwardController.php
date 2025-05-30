<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Award;
use App\Models\User;
use Illuminate\Http\Request;

class AwardController extends Controller
{

    public function index($userId)
    {
        $user = User::findOrFail($userId);
        return response()->json($user->awards);
    }

    public function store(Request $request)
{
    $validated = $request->validate([
        'prize' => 'required|string|max:255',
        'title' => 'required|string|max:255',
        'event' => 'nullable|string|max:255',
        'year' => 'required|digits:4|integer|min:1900|max:' . date('Y'),
        'location' => 'nullable|string|max:255',
    ]);

    $award = auth()->user()->awards()->create($validated);

    return response()->json($award, 201);
}


    public function update(Request $request, $awardId)
{
    $award = Award::findOrFail($awardId);

    // Ensure the authenticated user owns this award
    if ($award->user_id !== auth()->id()) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $validated = $request->validate([
        'prize' => 'sometimes|required|string|max:255',
        'title' => 'sometimes|required|string|max:255',
        'event' => 'nullable|string|max:255',
        'year' => 'sometimes|required|digits:4|integer|min:1900|max:' . date('Y'),
        'location' => 'nullable|string|max:255',
    ]);

    $award->update($validated);

    return response()->json($award);
}

public function destroy($awardId)
{
    $award = Award::findOrFail($awardId);

    // Ensure the authenticated user owns this award
    if ($award->user_id !== auth()->id()) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $award->delete();

    return response()->json(['message' => 'Award deleted successfully.']);
}

}
