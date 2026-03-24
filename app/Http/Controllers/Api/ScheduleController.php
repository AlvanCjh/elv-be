<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $projectId = $request->query('project_id');
        $query = Schedule::query()->with(['assignedToUser', 'createdBy']);
        
        if ($projectId) {
            $query->where(function ($q) use ($projectId) {
                $q->where('project_id', $projectId);
            });
        }
        
        return response()->json($query->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'assigned_to_user_id' => 'nullable|exists:users,id',
            'assigned_team' => 'nullable|string',
            'project_id' => 'required|exists:projects,id',
            'status' => 'string',
        ]);

        $validated['created_by_user_id'] = Auth::id() ?? 1;

        $schedule = Schedule::create($validated);
        return response()->json($schedule->load(['assignedToUser', 'createdBy']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return response()->json(Schedule::with(['assignedToUser', 'createdBy'])->findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $schedule = Schedule::findOrFail($id);
        
        $validated = $request->validate([
            'title' => 'string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'assigned_to_user_id' => 'nullable|exists:users,id',
            'assigned_team' => 'nullable|string',
            'status' => 'string',
        ]);

        $schedule->update($validated);
        return response()->json($schedule->load(['assignedToUser', 'createdBy']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->delete();
        return response()->json(null, 204);
    }
}
