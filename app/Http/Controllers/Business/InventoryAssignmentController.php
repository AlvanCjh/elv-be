<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Business\BusinessInventory;
use App\Models\Business\InventoryAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryAssignment::with(['inventory', 'user']);

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        return response()->json($query->latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'business_inventory_id' => 'required|exists:business_inventories,id',
            'user_id'               => 'required|exists:users,id',
            'quantity'              => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($validated) {
            $inventory = BusinessInventory::findOrFail($validated['business_inventory_id']);

            if ($inventory->quantity < $validated['quantity']) {
                return response()->json(['message' => 'Insufficient stock quantity'], 422);
            }

            // Deduct from total inventory
            $inventory->decrement('quantity', $validated['quantity']);

            $assignment = InventoryAssignment::create([
                'business_inventory_id' => $validated['business_inventory_id'],
                'user_id'               => $validated['user_id'],
                'quantity'              => $validated['quantity'],
                'assigned_at'           => now(),
                'status'                => 'active',
            ]);

            return response()->json($assignment->load(['inventory', 'user']), 201);
        });
    }

    public function myItems(Request $request)
    {
        $assignments = InventoryAssignment::where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->with('inventory')
            ->get();

        return response()->json($assignments);
    }

    public function returnItem($id)
    {
        return DB::transaction(function () use ($id) {
            $assignment = InventoryAssignment::findOrFail($id);

            if ($assignment->status === 'returned') {
                return response()->json(['message' => 'Item already returned'], 422);
            }

            $assignment->update(['status' => 'returned']);

            // Add back to inventory
            $inventory = $assignment->inventory;
            $inventory->increment('quantity', $assignment->quantity);

            return response()->json(['message' => 'Item returned successfully']);
        });
    }
}
