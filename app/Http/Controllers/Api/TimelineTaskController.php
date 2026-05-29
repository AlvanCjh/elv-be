<?php

namespace App\Http\Controllers\Api;

use App\Models\TimelineTask;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TimelineTaskController extends Controller
{
    /**
     * Display a listing of timeline tasks for the current project.
     */
    public function index(Request $request)
    {
        $projectId = $request->header('X-Project-Id');
        if (!$projectId) {
            return response()->json(['message' => 'Project ID header is required'], 400);
        }

        $query = TimelineTask::where('project_id', $projectId);

        if ($request->has('parent_id')) {
            $parentId = $request->query('parent_id');
            if ($parentId === 'null') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $parentId);
            }
        } elseif (!$request->has('all')) {
            // Default to root tasks if no parent_id and not requesting all
            $query->whereNull('parent_id');
        }

        $tasks = $query->get();
        return response()->json($tasks);
    }

    /**
     * Display the specified timeline task.
     */
    public function show(Request $request, $id)
    {
        $projectId = $request->header('X-Project-Id');
        $task = TimelineTask::where('id', $id)->where('project_id', $projectId)->firstOrFail();
        return response()->json($task);
    }

    /**
     * Store a newly created timeline task.
     */
    public function store(Request $request)
    {
        $projectId = $request->header('X-Project-Id');
        if (!$projectId) {
            return response()->json(['message' => 'Project ID header is required'], 400);
        }

        $validated = $request->validate([
            'parent_id' => 'nullable|exists:timeline_tasks,id',
            'name' => 'required|string|max:255',
            'expected_start_date' => 'required|date',
            'expected_end_date' => 'required|date|after_or_equal:expected_start_date',
            'actual_start_date' => 'nullable|date',
            'actual_end_date' => 'nullable|date|after_or_equal:actual_start_date',
        ]);

        $validated['project_id'] = $projectId;
        $task = TimelineTask::create($validated);

        return response()->json($task, 201);
    }

    /**
     * Update the specified timeline task (with edit comment requirement if dates change).
     */
    public function update(Request $request, $id)
    {
        $projectId = $request->header('X-Project-Id');
        $task = TimelineTask::where('id', $id)->where('project_id', $projectId)->firstOrFail();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'expected_start_date' => 'sometimes|date',
            'expected_end_date' => 'sometimes|date|after_or_equal:expected_start_date',
            'actual_start_date' => 'nullable|date',
            'actual_end_date' => 'nullable|date|after_or_equal:actual_start_date',
            'edit_reason' => 'sometimes|nullable|string',
        ]);

        // If user changed any date, require edit reason
        $dateChanged = false;
        $desc = [];
        $dateFields = [
            'expected_start_date' => 'Expected Start',
            'expected_end_date' => 'Expected End',
            'actual_start_date' => 'Actual Start',
            'actual_end_date' => 'Actual End'
        ];

        foreach ($dateFields as $field => $label) {
            if (isset($validated[$field]) && $validated[$field] != $task->{$field}) {
                $dateChanged = true;
                $oldVal = $task->{$field} ? date('Y-m-d', strtotime($task->{$field})) : 'N/A';
                $newVal = $validated[$field] ? date('Y-m-d', strtotime($validated[$field])) : 'N/A';
                $desc[] = "$label changed from $oldVal to $newVal";
            }
        }

        if ($dateChanged && empty($validated['edit_reason'])) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'edit_reason' => ['An edit reason is required when changing task dates.']
                ]
            ], 422);
        }

        $task->update($validated);

        if ($dateChanged) {
            \App\Models\TimelineHistory::create([
                'project_id' => $projectId,
                'timeline_task_id' => $task->id,
                'user_id' => $request->user()->id ?? 1, // Fallback for testing
                'entity_name' => $task->name,
                'change_details' => implode(", ", $desc),
                'reason' => $validated['edit_reason'] ?? 'Dragged on timeline',
            ]);
        }

        return response()->json($task);
    }

    /**
     * Get history of changes for the project.
     */
    public function history(Request $request)
    {
        $projectId = $request->header('X-Project-Id');
        if (!$projectId) {
            return response()->json(['message' => 'Project ID header is required'], 400);
        }

        $history = \App\Models\TimelineHistory::with('user')
            ->where('project_id', $projectId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($history);
    }

    /**
     * Remove the specified timeline task.
     */
    public function destroy(Request $request, $id)
    {
        $projectId = $request->header('X-Project-Id');
        $task = TimelineTask::where('id', $id)->where('project_id', $projectId)->firstOrFail();
        
        $task->delete();

        return response()->json(['message' => 'Task deleted successfully']);
    }
}
