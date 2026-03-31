<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EquipmentChecksheet;

class EquipmentChecksheetController extends Controller
{
    public function index(Request $request)
    {
        $query = EquipmentChecksheet::query();
        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        return response()->json($query->orderBy('created_at', 'desc')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'equipment_name' => 'required|string',
            'equipment_type' => 'required|string',
            'model_number' => 'nullable|string',
            'serial_number' => 'nullable|string',
            'location' => 'nullable|string',
            'voltage_v' => 'nullable|string',
            'current_a' => 'nullable|string',
            'other_readings' => 'nullable|string',
            'checklist_results' => 'nullable|array',
            'status' => 'nullable|string',
            'checked_by' => 'nullable|string',
            'check_date' => 'nullable|date',
            'remarks' => 'nullable|string',
        ]);

        $checksheet = EquipmentChecksheet::create($validated);
        return response()->json($checksheet, 201);
    }

    public function show($id)
    {
        return response()->json(EquipmentChecksheet::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $checksheet = EquipmentChecksheet::findOrFail($id);
        $validated = $request->validate([
            'equipment_name' => 'sometimes|required|string',
            'equipment_type' => 'sometimes|required|string',
            'model_number' => 'nullable|string',
            'serial_number' => 'nullable|string',
            'location' => 'nullable|string',
            'voltage_v' => 'nullable|string',
            'current_a' => 'nullable|string',
            'other_readings' => 'nullable|string',
            'checklist_results' => 'nullable|array',
            'status' => 'sometimes|required|string',
            'checked_by' => 'nullable|string',
            'check_date' => 'nullable|date',
            'remarks' => 'nullable|string',
        ]);

        $checksheet->update($validated);
        return response()->json($checksheet);
    }

    public function destroy($id)
    {
        $checksheet = EquipmentChecksheet::findOrFail($id);
        $checksheet->delete();
        return response()->json(['message' => 'Checksheet deleted']);
    }
}
