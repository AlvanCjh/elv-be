<?php

namespace App\Http\Controllers;

use App\Models\RiskAssessment;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RiskAssessmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $project_id = $request->header('X-Project-Id');
        $reports = RiskAssessment::with(['createdBy', 'project'])
            ->where('project_id', $project_id)
            ->latest()
            ->get();
        return response()->json($reports);
    }

    public function store(Request $request)
    {
        $project_id = $request->header('X-Project-Id');
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:fire,hazard,health,security,environmental,other',
            'risk_level' => 'required|in:low,medium,high,extreme',
            'description' => 'nullable|string',
            'mitigation_plan' => 'nullable|string',
            'status' => 'required|in:open,in review,mitigated,closed',
            'assessment_date' => 'nullable|date',
        ]);

        $report = RiskAssessment::create([
            'project_id' => $project_id,
            'created_by_user_id' => auth()->id(),
            'title' => $validated['title'],
            'type' => $validated['type'],
            'risk_level' => $validated['risk_level'],
            'description' => $validated['description'],
            'mitigation_plan' => $validated['mitigation_plan'],
            'status' => $validated['status'],
            'assessment_date' => $validated['assessment_date'] ?? now(),
        ]);

        return response()->json($report->load(['createdBy', 'project']), 201);
    }

    public function show(RiskAssessment $riskAssessment)
    {
        return response()->json($riskAssessment->load(['createdBy', 'project']));
    }

    public function update(Request $request, RiskAssessment $riskAssessment)
    {
        $validated = $request->validate([
            'title' => 'string|max:255',
            'type' => 'in:fire,hazard,health,security,environmental,other',
            'risk_level' => 'in:low,medium,high,extreme',
            'description' => 'nullable|string',
            'mitigation_plan' => 'nullable|string',
            'status' => 'in:open,in review,mitigated,closed',
            'assessment_date' => 'date',
        ]);

        $riskAssessment->update($validated);

        return response()->json($riskAssessment->load(['createdBy', 'project']));
    }

    public function destroy(RiskAssessment $riskAssessment)
    {
        $riskAssessment->delete();
        return response()->json(null, 204);
    }
}
