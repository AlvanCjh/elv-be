<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Business\BusinessInventory;
use App\Models\Business\InventoryStockIn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryStockInController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryStockIn::with(['inventory', 'user']);

        if ($request->has('business_inventory_id')) {
            $query->where('business_inventory_id', $request->business_inventory_id);
        }

        return response()->json($query->latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'business_inventory_id' => 'required|exists:business_inventories,id',
            'quantity'              => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $inventory = BusinessInventory::findOrFail($validated['business_inventory_id']);
            
            // Add to total inventory
            $inventory->increment('quantity', $validated['quantity']);

            $stockIn = InventoryStockIn::create([
                'business_inventory_id' => $validated['business_inventory_id'],
                'user_id'               => $request->user()->id,
                'quantity'              => $validated['quantity'],
            ]);

            return response()->json($stockIn->load(['inventory', 'user']), 201);
        });
    }
}
