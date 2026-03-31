<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Floor;
use App\Models\Zone;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    public function index($floor_id)
    {
        return response()->json(Zone::where('floor_id', $floor_id)->get());
    }

    /**
     * Store a newly created zone in storage.
     */
    public function store(Request $request, $floor_id)
    {
        $request->validate([
            'alias_id' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'svg_path' => 'required|string',
            'area' => 'nullable|string|max:255',
            'location_desc' => 'nullable|string',
        ]);

        $floor = Floor::findOrFail($floor_id);

        $zone = new Zone();
        $zone->floor_id = $floor->id;
        $zone->alias_id = $request->alias_id;
        $zone->name = $request->name;
        $zone->svg_path = $request->svg_path;
        $zone->area = $request->area;
        $zone->location_desc = $request->location_desc;
        $zone->save();

        return response()->json([
            'message' => 'Zone created successfully',
            'zone' => $zone,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $zone = Zone::findOrFail($id);
        $zone->update($request->all());
        return response()->json([
            'message' => 'Zone updated successfully',
            'zone' => $zone
        ]);
    }

    /**
     * Remove the specified zone from storage.
     */
    public function destroy($id)
    {
        $zone = Zone::findOrFail($id);
        $zone->delete();

        return response()->json([
            'message' => 'Zone deleted successfully'
        ]);
    }
}
