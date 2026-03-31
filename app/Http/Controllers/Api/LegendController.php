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
        
        $projectId = app('active_project_id');

        if ($request->has('floor_ids') && !empty($request->floor_ids)) {
            $legend->floors()->sync($request->floor_ids);
        } else {
            // Assign to ALL floors in the current project
            $allFloorIds = \App\Models\Floor::whereHas('building', function ($q) use ($projectId) {
                $q->where('project_id', $projectId);
            })->pluck('id');
            
            $legend->floors()->sync($allFloorIds);
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
