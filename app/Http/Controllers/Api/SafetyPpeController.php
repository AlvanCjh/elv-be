<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SafetyPpe;

class SafetyPpeController extends Controller
{
    public function index(Request $request)
    {
        $query = SafetyPpe::with('assignee')->latest();
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'item_name' => 'required|string|max:255',
            'status' => 'required|string|in:Available,In Use,Defective',
            'assigned_to' => 'nullable|exists:users,id',
            'remarks' => 'nullable|string',
        ]);

        $ppe = SafetyPpe::create($data);
        return response()->json($ppe->load('assignee'), 201);
    }

    public function update(Request $request, $id)
    {
        $ppe = SafetyPpe::findOrFail($id);

        $data = $request->validate([
            'item_name' => 'sometimes|required|string|max:255',
            'status' => 'sometimes|required|string|in:Available,In Use,Defective',
            'assigned_to' => 'sometimes|nullable|exists:users,id',
            'remarks' => 'sometimes|nullable|string',
        ]);

        $ppe->update($data);
        return response()->json($ppe->load('assignee'));
    }

    public function destroy($id)
    {
        $ppe = SafetyPpe::findOrFail($id);
        $ppe->delete();
        return response()->json(null, 204);
    }
}
