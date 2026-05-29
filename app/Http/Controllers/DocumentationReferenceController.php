<?php 

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DocumentationReference;

class DocumentationReferenceController extends Controller
{
    public function index(Request $request)
    {
        $query = DocumentationReference::query();
        
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('location_stored', 'like', "%{$search}%");
        }

        return response()->json($query->orderBy('created_at', 'desc')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location_stored' => 'required|string|max:255',
        ]);

        $doc = DocumentationReference::create($validated);
        return response()->json($doc, 201);
    }

    public function update(Request $request, $id)
    {
        $doc = DocumentationReference::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'location_stored' => 'sometimes|required|string|max:255',
        ]);

        $doc->update($validated);
        return response()->json($doc);
    }

    public function destroy($id)
    {
        $doc = DocumentationReference::findOrFail($id);
        $doc->delete();
        return response()->json(null, 204);
    }
}
