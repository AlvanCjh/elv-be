<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Attendance;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $projectId = $request->query('project_id');
        $query = Attendance::with('user');
        
        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required', // Allow either numeric string or UUID string
            'project_id' => 'required|integer',
            'date' => 'required|date',
            'status' => 'required|string',
            'remarks' => 'nullable|string',
        ]);

        $attendance = Attendance::create($validated);
        return response()->json($attendance->load('user'), 201);
    }

    public function show(Attendance $attendance)
    {
        return response()->json($attendance->load('user'));
    }

    public function update(Request $request, Attendance $attendance)
    {
        $validated = $request->validate([
            'user_id' => 'sometimes|string',
            'project_id' => 'sometimes|integer',
            'date' => 'sometimes|date',
            'status' => 'sometimes|string',
            'remarks' => 'nullable|string',
        ]);

        $attendance->update($validated);
        return response()->json($attendance->load('user'));
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();
        return response()->json(null, 204);
    }
}
