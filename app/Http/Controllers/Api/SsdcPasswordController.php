<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SsdcPassword;

class SsdcPasswordController extends Controller
{
    public function index(Request $request)
    {
        $query = SsdcPassword::query();
        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'system_name' => 'required|string',
            'username' => 'nullable|string',
            'password' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $password = SsdcPassword::create($validated);
        return response()->json($password, 201);
    }

    public function update(Request $request, $id)
    {
        $password = SsdcPassword::findOrFail($id);
        $validated = $request->validate([
            'system_name' => 'sometimes|required|string',
            'username' => 'nullable|string',
            'password' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $password->update($validated);
        return response()->json($password);
    }

    public function destroy($id)
    {
        $password = SsdcPassword::findOrFail($id);
        $password->delete();
        return response()->json(['message' => 'Password record deleted']);
    }
}
