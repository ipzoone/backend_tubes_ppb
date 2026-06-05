<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use Illuminate\Http\Request;

class SkillController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(Skill::where('user_id', $request->user()->id)->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'level' => 'required|numeric|min:0|max:100',
            'status' => 'required|string',
        ]);

        $validated['user_id'] = $request->user()->id;

        $skill = Skill::create($validated);
        return response()->json($skill, 201);
    }

    public function show(Request $request, $id)
    {
        $skill = Skill::where('id', $id)->where('user_id', $request->user()->id)->firstOrFail();
        return response()->json($skill);
    }

    public function update(Request $request, $id)
    {
        $skill = Skill::where('id', $id)->where('user_id', $request->user()->id)->firstOrFail();
        
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'level' => 'sometimes|required|numeric|min:0|max:100',
            'status' => 'sometimes|required|string',
        ]);

        $skill->update($validated);
        return response()->json($skill);
    }

    public function destroy(Request $request, $id)
    {
        $skill = Skill::where('id', $id)->where('user_id', $request->user()->id)->firstOrFail();
        $skill->delete();
        return response()->json(['message' => 'Skill deleted successfully']);
    }
}
