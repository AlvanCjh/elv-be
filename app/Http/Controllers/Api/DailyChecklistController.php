<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DailyChecklist;

class DailyChecklistController extends Controller
{
    public function index(Request $request)
    {
        $query = DailyChecklist::query();
        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->has('company_type')) {
            $query->where('company_type', $request->company_type);
        }
        return response()->json($query->orderBy('check_date', 'desc')->orderBy('created_at', 'desc')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'company_type' => 'required|string',
            'check_date' => 'required|date',
            'attendee_name' => 'nullable|string',
            'verified_by' => 'nullable|string',
            'status_summary' => 'nullable|string',
            'sections_data' => 'required|array',
            'remarks' => 'nullable|string',
        ]);

        $checklist = DailyChecklist::create($validated);
        return response()->json($checklist, 201);
    }

    public function show($id)
    {
        return response()->json(DailyChecklist::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $checklist = DailyChecklist::findOrFail($id);
        $validated = $request->validate([
            'company_type' => 'sometimes|required|string',
            'check_date' => 'sometimes|required|date',
            'attendee_name' => 'nullable|string',
            'verified_by' => 'nullable|string',
            'status_summary' => 'nullable|string',
            'sections_data' => 'sometimes|required|array',
            'remarks' => 'nullable|string',
        ]);

        $checklist->update($validated);
        return response()->json($checklist);
    }

    public function destroy($id)
    {
        $checklist = DailyChecklist::findOrFail($id);
        $checklist->delete();
        return response()->json(['message' => 'Daily checklist deleted']);
    }
}
