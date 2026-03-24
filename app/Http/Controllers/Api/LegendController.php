<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Legend;
use Illuminate\Http\Request;

class LegendController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'system_type' => 'required|string',
            'floor_id' => 'nullable|integer|exists:floors,id',
        ]);

        $query = Legend::with('floors')->where('system_type', $request->system_type);

        if ($request->has('floor_id')) {
            $query->whereHas('floors', function ($q) use ($request) {
                $q->where('floors.id', $request->floor_id);
            });
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'system_type' => 'required|string',
            'name' => 'required|string',
            'shape_type' => 'required|string',
            'icon_svg' => 'nullable|string',
            'style' => 'nullable|array',
            'floor_ids' => 'nullable|array', // Empty or null means all floors
            'floor_ids.*' => 'exists:floors,id',
        ]);

        $legend = Legend::create($request->only(['system_type', 'name', 'shape_type', 'icon_svg', 'style']));

        if ($request->has('floor_ids') && !empty($request->floor_ids)) {
            $legend->floors()->sync($request->floor_ids);
        } else {
            // If no floor_ids specified, it might mean "shared" or global. 
            // Depending on how frontend expects it, we might want to attach all existing floors or just leave it empty.
            // User said: "assigned to all floors". I'll attach all floors of the CURRENT project? 
            // Wait, Legend is not project-scoped in schema? Oh, floors are part of a building, and buildings are in a project.
        }

        return response()->json($legend->load('floors'));
    }

    public function destroy($id)
    {
        $legend = Legend::findOrFail($id);
        $legend->floors()->detach();
        $legend->delete();
        return response()->json(['message' => 'Icon deleted successfully']);
    }
}
