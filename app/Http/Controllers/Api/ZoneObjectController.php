<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ZoneObject;
use Illuminate\Http\Request;

class ZoneObjectController extends Controller
{
    // GET /zones/{zoneId}/objects
    public function index($zoneId)
    {
        return response()->json(ZoneObject::where('zone_id', $zoneId)->orderBy('created_at')->get());
    }

    // POST /zones/{zoneId}/objects
    public function store(Request $request, $zoneId)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:200',
            'description' => 'nullable|string',
        ]);
        $obj = ZoneObject::create(array_merge($data, ['zone_id' => $zoneId]));
        return response()->json($obj, 201);
    }

    // DELETE /objects/{id}
    public function destroy($id)
    {
        ZoneObject::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}
