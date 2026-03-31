<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FloorDrawingRevisionController extends Controller
{
    public function index(Request $request)
    {
        $floorId = $request->query('floor_id');
        if (!$floorId) {
            return response()->json(['message' => 'floor_id is required'], 400);
        }

        $revisions = \App\Models\FloorDrawingRevision::with(['creator', 'approver'])
            ->where('floor_id', $floorId)
            ->orderBy('revision_date', 'desc')
            ->get();

        return response()->json($revisions);
    }

    public function store(Request $request)
    {
        $request->validate([
            'floor_id' => 'required|exists:floors,id',
            'version_name' => 'required|string',
            'revision_date' => 'required|date',
            'file_content' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        $revision = \App\Models\FloorDrawingRevision::create([
            'floor_id' => $request->floor_id,
            'version_name' => $request->version_name,
            'revision_date' => $request->revision_date,
            'file_content' => $request->file_content,
            'remarks' => $request->remarks,
            'status' => 'pending',
            'created_by' => $request->user()->id,
        ]);

        return response()->json($revision, 201);
    }

    public function show($id)
    {
        $revision = \App\Models\FloorDrawingRevision::with(['creator', 'approver'])->findOrFail($id);
        return response()->json($revision);
    }

    public function update(Request $request, $id)
    {
        $revision = \App\Models\FloorDrawingRevision::findOrFail($id);
        
        if ($request->has('status') && $request->status === 'active') {
            // Deactivate other revisions for this floor
            \App\Models\FloorDrawingRevision::where('floor_id', $revision->floor_id)
                ->where('id', '!=', $id)
                ->update(['status' => 'archived']);

            $revision->status = 'active';
            $revision->approved_by = $request->user()->id;
            $revision->save();

            // Sync with Floor model
            $floor = \App\Models\Floor::find($revision->floor_id);
            if ($floor && $revision->file_content) {
                $floor->floor_plan_svg = $revision->file_content;
                $floor->save();
            }

            return response()->json(['message' => 'Revision activated', 'revision' => $revision]);
        }

        $revision->update($request->only(['version_name', 'revision_date', 'remarks', 'file_content']));
        return response()->json($revision);
    }

    public function destroy($id)
    {
        $revision = \App\Models\FloorDrawingRevision::findOrFail($id);
        if ($revision->status === 'active') {
            return response()->json(['message' => 'Cannot delete active revision'], 400);
        }
        $revision->delete();
        return response()->json(['message' => 'Revision deleted']);
    }
}
