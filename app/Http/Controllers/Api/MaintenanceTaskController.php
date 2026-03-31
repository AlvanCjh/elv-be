<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MaintenanceTask;

class MaintenanceTaskController extends Controller
{
    public function index(Request $request)
    {
        $query = MaintenanceTask::query();
        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'type' => 'required|in:internal,external',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|string',
            'date' => 'nullable|date',
            'status' => 'nullable|string',
        ]);

        $task = MaintenanceTask::create($validated);
        return response()->json($task, 201);
    }

    public function update(Request $request, $id)
    {
        $task = MaintenanceTask::findOrFail($id);
        $validated = $request->validate([
            'type' => 'sometimes|required|in:internal,external',
            'title' => 'sometimes|required|string',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|string',
            'date' => 'nullable|date',
            'status' => 'sometimes|required|string',
        ]);

        $task->update($validated);
        return response()->json($task);
    }

    public function destroy($id)
    {
        $task = MaintenanceTask::findOrFail($id);
        $task->delete();
        return response()->json(['message' => 'Maintenance task deleted']);
    }
}
