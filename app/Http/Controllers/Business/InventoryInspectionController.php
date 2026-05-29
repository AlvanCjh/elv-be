<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Business\InventoryInspection;
use App\Models\Business\InventoryAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InventoryInspectionController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryInspection::with(['assignment.inventory', 'user']);

        return response()->json($query->latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'inventory_assignment_id' => 'required|exists:inventory_assignments,id',
            'condition_status'        => 'required|in:Good,Broken,Needs Repair',
            'photo'                   => 'nullable|image|max:5120',
            'remarks'                  => 'nullable|string',
        ]);

        $photoUrl = null;
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('inventory-inspections', 'public');
            $photoUrl = asset('storage/' . $path);
        }

        $inspection = InventoryInspection::create([
            'inventory_assignment_id' => $validated['inventory_assignment_id'],
            'user_id'                 => $request->user()->id,
            'condition_status'        => $validated['condition_status'],
            'photo_url'               => $photoUrl,
            'remarks'                 => $validated['remarks'],
        ]);

        return response()->json($inspection->load(['assignment.inventory', 'user']), 201);
    }
}
