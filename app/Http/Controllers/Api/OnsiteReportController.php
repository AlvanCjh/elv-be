<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OnsiteReport;
use Illuminate\Support\Facades\Storage;

class OnsiteReportController extends Controller
{
    public function index(Request $request)
    {
        $query = OnsiteReport::with(['uploader', 'assigned_to_user'])->latest();
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'project_id'          => 'required|exists:projects,id',
            'category'           => 'required|string',
            'title'              => 'required|string|max:255',
            'rfwi_ref_no'        => 'nullable|string|max:255',
            'location'           => 'nullable|string|max:255',
            'gridline_zone'      => 'nullable|string|max:255',
            'date_inspected'     => 'nullable|date',
            'consultant_comments' => 'nullable|string',
            'description'        => 'nullable|string',
            'status'             => 'nullable|string|in:approve,approve with comment,rejected,standby,pending',
            'assigned_to'        => 'nullable|exists:users,id',
            'file'               => 'nullable|file|max:10240',
        ]);

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('onsite-reports', 'public');
        }
        $data['uploaded_by'] = $request->user()->id;
        unset($data['file']);

        $doc = OnsiteReport::create($data);
        return response()->json($doc->load(['uploader', 'assigned_to_user']), 201);
    }

    public function update(Request $request, $id)
    {
        $report = OnsiteReport::findOrFail($id);
        $data = $request->validate([
            'title'              => 'sometimes|string|max:255',
            'rfwi_ref_no'        => 'sometimes|nullable|string|max:255',
            'location'           => 'sometimes|nullable|string|max:255',
            'gridline_zone'      => 'sometimes|nullable|string|max:255',
            'date_inspected'     => 'sometimes|nullable|date',
            'consultant_comments' => 'sometimes|nullable|string',
            'description'        => 'sometimes|nullable|string',
            'status'             => 'sometimes|string|in:approve,approve with comment,rejected,standby,pending',
            'assigned_to'        => 'sometimes|nullable|exists:users,id',
            'file'               => 'sometimes|nullable|file|max:10240',
        ]);

        if ($request->hasFile('file')) {
            if ($report->file_path) {
                Storage::disk('public')->delete($report->file_path);
            }
            $data['file_path'] = $request->file('file')->store('onsite-reports', 'public');
        }
        unset($data['file']);

        $report->update($data);
        return response()->json($report->load(['uploader', 'assigned_to_user']));
    }

    public function destroy($id)
    {
        $doc = OnsiteReport::findOrFail($id);
        if ($doc->file_path) {
            Storage::disk('public')->delete($doc->file_path);
        }
        $doc->delete();
        return response()->json(null, 204);
    }
}
