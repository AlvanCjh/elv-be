<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SafetyAgenda;

class SafetyAgendaController extends Controller
{
    public function index(Request $request)
    {
        $query = SafetyAgenda::with('creator')->latest();
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
            'agenda_date' => 'required|date',
        ]);

        $data['created_by'] = $request->user()->id;

        $agenda = SafetyAgenda::create($data);
        return response()->json($agenda->load('creator'), 201);
    }

    public function destroy($id)
    {
        $agenda = SafetyAgenda::findOrFail($id);
        $agenda->delete();
        return response()->json(null, 204);
    }
}
