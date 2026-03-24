<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Floor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BuildingController extends Controller
{
    public function index()
    {
        return response()->json(Building::with('floors')->get());
    }

    public function show($id)
    {
        return response()->json(Building::with('floors')->findOrFail($id));
    }

    public function showFloor($id)
    {
        return response()->json(Floor::with(['zones.objectComponents.latestStatus'])->findOrFail($id));
    }

    public function uploadFloorImage(Request $request, $id)
    {
        $request->validate([
            'image' => 'required|mimes:jpeg,png,jpg,svg|max:20480',
            'system' => 'nullable|string|in:bss,pa,telco',
        ]);

        $floor = Floor::findOrFail($id);
        $system = $request->input('system');

        if ($request->hasFile('image')) {
            $column = 'floor_plan_image_url';
            if ($system === 'bss') $column = 'bss_plan_image_url';
            else if ($system === 'pa') $column = 'pa_plan_image_url';
            else if ($system === 'telco') $column = 'telco_plan_image_url';

            if ($floor->$column) {
                // Determine existing path to delete
                $oldPath = str_replace(asset('storage/'), '', $floor->$column);
                Storage::disk('public')->delete($oldPath);
            }

            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('floor_maps', $filename, 'public');

            $floor->$column = asset('storage/' . $path);
            $floor->save();

            return response()->json(['message' => 'Floor plan replaced successfully', 'floor' => $floor]);
        }

        return response()->json(['error' => 'No image uploaded'], 400);
    }

    public function uploadElevationImage(Request $request, $id)
    {
        $request->validate([
            'image' => 'required|mimes:jpeg,png,jpg,svg|max:20480',
        ]);

        $building = Building::findOrFail($id);

        if ($request->hasFile('image')) {
            if ($building->elevation_image_url) {
                $oldPath = str_replace(asset('storage/'), '', $building->elevation_image_url);
                Storage::disk('public')->delete($oldPath);
            }

            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('building_elevations', $filename, 'public');

            $building->elevation_image_url = asset('storage/' . $path);
            $building->save();

            return response()->json(['message' => 'Building elevation replaced successfully', 'building' => $building]);
        }

        return response()->json(['error' => 'No image uploaded'], 400);
    }

    public function storeFloor(Request $request, $buildingId)
    {
        $request->validate([
            'floor_number' => 'required|string',
            'type' => 'nullable|string',
            'floor_plan_svg' => 'required|string',
        ]);

        $floor = new Floor();
        $floor->building_id = $buildingId;
        $floor->floor_number = $request->input('floor_number');
        $floor->type = $request->input('type');
        $floor->floor_plan_svg = $request->input('floor_plan_svg');
        $floor->save();

        return response()->json(['message' => 'Floor created successfully', 'floor' => $floor]);
    }

    public function deleteFloor($id)
    {
        $floor = Floor::findOrFail($id);
        $floor->delete();

        return response()->json(['message' => 'Floor deleted successfully']);
    }
}