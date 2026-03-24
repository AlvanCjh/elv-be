<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MaintenanceReport;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class MaintenanceReportController extends Controller
{
    public function index(Request $request)
    {
        $project_id = $request->header('X-Project-Id');
        
        $query = MaintenanceReport::with(['assignedToUser', 'createdBy'])
            ->where('project_id', $project_id);

        if ($request->has('start_date')) {
            $query->whereDate('maintenance_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('maintenance_date', '<=', $request->end_date);
        }

        $reports = $query->latest()
            ->get();
            
        return response()->json($reports);
    }

    public function store(Request $request)
    {
        $project_id = $request->header('X-Project-Id');
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to_user_id' => 'required|exists:users,id',
            'maintenance_date' => 'required|date',
            'status' => 'required|in:pending,completed',
            'image' => 'required|image|max:10240', // Max 10MB
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('maintenance', 'public');
        }

        $report = MaintenanceReport::create([
            'project_id' => $project_id,
            'assigned_to_user_id' => $validated['assigned_to_user_id'],
            'created_by_user_id' => Auth::id(),
            'title' => $validated['title'],
            'description' => $validated['description'],
            'image_path' => $imagePath,
            'status' => $validated['status'],
            'maintenance_date' => $validated['maintenance_date'],
        ]);

        return response()->json($report->load(['assignedToUser', 'createdBy']), 201);
    }

    public function show(MaintenanceReport $maintenanceReport)
    {
        return response()->json($maintenanceReport->load(['assignedToUser', 'createdBy', 'project']));
    }

    public function update(Request $request, MaintenanceReport $maintenanceReport)
    {
        $validated = $request->validate([
            'title' => 'string|max:255',
            'description' => 'nullable|string',
            'assigned_to_user_id' => 'exists:users,id',
            'maintenance_date' => 'date',
            'status' => 'in:pending,completed',
            'image' => 'nullable|image|max:10240',
        ]);

        if ($request->hasFile('image')) {
            // Delete old file if exists
            if ($maintenanceReport->image_path && Storage::disk('public')->exists($maintenanceReport->image_path)) {
                Storage::disk('public')->delete($maintenanceReport->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('maintenance', 'public');
        }

        $maintenanceReport->update($validated);

        return response()->json($maintenanceReport->load(['assignedToUser', 'createdBy']));
    }

    public function destroy(MaintenanceReport $maintenanceReport)
    {
        if ($maintenanceReport->image_path && Storage::disk('public')->exists($maintenanceReport->image_path)) {
            Storage::disk('public')->delete($maintenanceReport->image_path);
        }
        
        $maintenanceReport->delete();
        
        return response()->json(null, 204);
    }
}
