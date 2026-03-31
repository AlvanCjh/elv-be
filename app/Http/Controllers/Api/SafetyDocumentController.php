<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SafetyDocument;
use Illuminate\Support\Facades\Storage;

class SafetyDocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = SafetyDocument::with('uploader')->latest();
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'required|file|max:10240',
        ]);

        $data['file_path'] = $request->file('file')->store('safety-documents', 'public');
        $data['uploaded_by'] = $request->user()->id;
        unset($data['file']);

        $doc = SafetyDocument::create($data);
        return response()->json($doc->load('uploader'), 201);
    }

    public function destroy($id)
    {
        $doc = SafetyDocument::findOrFail($id);
        if ($doc->file_path) {
            Storage::disk('public')->delete($doc->file_path);
        }
        $doc->delete();
        return response()->json(null, 204);
    }
}
