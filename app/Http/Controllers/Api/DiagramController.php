<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DrawingDiagram;
use App\Models\SchematicDiagram;
use Illuminate\Support\Facades\Storage;

class DiagramController extends Controller
{
    private function getModel($type)
    {
        return $type === 'drawing' ? new DrawingDiagram() : new SchematicDiagram();
    }

    public function index(Request $request, $type)
    {
        $model = $this->getModel($type);
        $query = $model::with('uploader');

        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        return response()->json($query->orderBy('created_at', 'desc')->get());
    }

    public function store(Request $request, $type)
    {
        $request->validate([
            'project_title' => 'required|string',
            'status' => 'required|string',
            'pdf_file' => 'nullable|file|mimes:pdf|max:10240',
            'project_id' => 'nullable|exists:projects,id'
        ]);

        $filePath = null;
        if ($request->hasFile('pdf_file')) {
            $filePath = $request->file('pdf_file')->store('diagrams', 'public');
        }

        $model = $this->getModel($type);
        $diagram = $model::create([
            'project_id' => $request->project_id,
            'uploaded_by_user_id' => $request->user()->id,
            'project_title' => $request->project_title,
            'status' => $request->status,
            'file_path' => $filePath,
        ]);

        return response()->json($diagram->load('uploader'), 201);
    }

    public function update(Request $request, $type, $id)
    {
        $model = $this->getModel($type);
        $diagram = $model::findOrFail($id);

        $request->validate([
            'project_title' => 'sometimes|required|string',
            'status' => 'sometimes|required|string',
            'pdf_file' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        if ($request->hasFile('pdf_file')) {
            if ($diagram->file_path) {
                Storage::disk('public')->delete($diagram->file_path);
            }
            $diagram->file_path = $request->file('pdf_file')->store('diagrams', 'public');
        }

        if ($request->has('project_title')) $diagram->project_title = $request->project_title;
        if ($request->has('status')) $diagram->status = $request->status;

        $diagram->save();

        return response()->json($diagram->load('uploader'));
    }

    public function destroy($type, $id)
    {
        $model = $this->getModel($type);
        $diagram = $model::findOrFail($id);

        if ($diagram->file_path) {
            Storage::disk('public')->delete($diagram->file_path);
        }

        $diagram->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
