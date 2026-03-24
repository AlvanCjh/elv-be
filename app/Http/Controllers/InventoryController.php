<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Tool;
use App\Models\UsageRecord;
use App\Models\StockInRecord;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    private function getModel($type)
    {
        return $type === 'tool' ?Tool::class : Material::class;
    }

    public function index(Request $request)
    {
        $type = $request->query('type', 'material');
        $model = $this->getModel($type);
        return $model::orderBy('created_at', 'desc')->get();
    }

    public function store(Request $request)
    {
        $type = $request->query('type', 'material');
        $data = $request->validate([
            'material_name' => 'nullable|string', // material specific
            'tool_name' => 'nullable|string', // tool specific
            'name' => 'nullable|string', // Generic fallback
            'unit_of_measure' => $type === 'material' ? 'required|string' : 'nullable|string',
            'quantity_in_stock' => 'required|numeric',
            'brand' => 'nullable|string',
            'location' => 'nullable|string',
            'description' => 'nullable|string',
            'status' => 'nullable|string',
            'type' => 'nullable|string' // electronic, handy, devices
        ]);

        // Normalize name
        if ($type === 'tool') {
            $data['tool_name'] = $data['name'] ?? $data['tool_name'];
            unset($data['name']);
            // unset unit_of_measure if not needed or let it be null
            unset($data['unit_of_measure']);
        }
        else {
            $data['material_name'] = $data['name'] ?? $data['material_name'];
            unset($data['name']);
        }

        $model = $this->getModel($type);

        // Bind to current project scope
        $data['project_id'] = request()->header('X-Project-Id');

        $item = $model::create($data);

        return response()->json($item, 201);
    }

    public function update(Request $request, $id)
    {
        $type = $request->query('type', 'material');
        $model = $this->getModel($type);
        $item = $model::findOrFail($id);

        $data = $request->validate([
            'material_name' => 'nullable|string',
            'tool_name' => 'nullable|string',
            'name' => 'nullable|string',
            'unit_of_measure' => $type === 'material' ? 'required|string' : 'nullable|string',
            'quantity_in_stock' => 'required|numeric',
            'brand' => 'nullable|string',
            'location' => 'nullable|string',
            'description' => 'nullable|string',
            'status' => 'nullable|string',
            'type' => 'nullable|string'
        ]);

        if ($type === 'tool') {
            if (isset($data['name']))
                $data['tool_name'] = $data['name'];
            unset($data['unit_of_measure']);
        }
        else {
            if (isset($data['name']))
                $data['material_name'] = $data['name'];
        }
        unset($data['name']);

        $item->update($data);

        return response()->json($item);
    }

    public function destroy(Request $request, $id)
    {
        $type = $request->query('type', 'material');
        $model = $this->getModel($type);
        $model::destroy($id);
        return response()->json(null, 204);
    }

    public function stockIn(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required',
            'type' => 'nullable|in:material,tool',
            'quantity' => 'required|numeric|min:0',
            'date' => 'nullable|date',
            'remarks' => 'nullable|string'
        ]);

        $type = $validated['type'] ?? 'material';
        $model = $this->getModel($type);
        $item = $model::findOrFail($validated['id']);

        $item->quantity_in_stock += $validated['quantity'];
        $item->save();

        $item->stockInRecords()->create([
            'date_in' => $validated['date'] ?? now(),
            'in_qty' => $validated['quantity'],
            'remarks' => $validated['remarks'] ?? null
        ]);

        return response()->json($item);
    }

    public function stockOut(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required',
            'type' => 'nullable|in:material,tool',
            'quantity' => 'required|numeric|min:0',
            'date' => 'nullable|date',
            'user_id' => 'nullable',
            'remarks' => 'nullable|string',
            'expected_return_date' => 'nullable|date',
            'status' => 'nullable|string'
        ]);

        $type = $validated['type'] ?? 'material';
        $model = $this->getModel($type);
        $item = $model::findOrFail($validated['id']);

        if ($item->quantity_in_stock < $validated['quantity']) {
            return response()->json(['message' => 'Insufficient stock'], 400);
        }

        $item->quantity_in_stock -= $validated['quantity'];
        $item->save();

        $usageData = [
            'user_id' => $validated['user_id'] ?? auth()->id(),
            'date_out' => $validated['date'] ?? now(),
            'out_qty' => $validated['quantity'],
            'remarks' => $validated['remarks'] ?? null
        ];

        if ($type === 'tool') {
            $usageData['expected_return_date'] = $validated['expected_return_date'] ?? null;
            $usageData['status'] = $validated['status'] ?? 'borrowed';
        }

        $item->usageRecords()->create($usageData);

        return response()->json($item);
    }

    public function history(Request $request)
    {
        // Support filtering by type if needed, but for now show all history
        // Or if we want separate history per view, we can filter

        $stockIn = StockInRecord::with(['item'])->orderBy('created_at', 'desc')->get();
        // Transform items to generic structure or let frontend handle it

        $stockOut = UsageRecord::with(['item', 'user'])->orderBy('created_at', 'desc')->get();

        return response()->json([
            'stock_in' => $stockIn,
            'stock_out' => $stockOut
        ]);
    }

    public function returnTool(Request $request)
    {
        $validated = $request->validate([
            'usage_record_id' => 'required',
            'quantity' => 'required|numeric|min:1',
            'remarks' => 'nullable|string'
        ]);

        $usageRecord = UsageRecord::findOrFail($validated['usage_record_id']);

        // Ensure not returning more than borrowed
        $returnedSoFar = $usageRecord->return_qty ?? 0;
        $remaining = $usageRecord->out_qty - $returnedSoFar;

        if ($validated['quantity'] > $remaining) {
            return response()->json(['message' => 'Return quantity exceeds remaining borrowed quantity'], 400);
        }

        $usageRecord->return_qty = $returnedSoFar + $validated['quantity'];
        $usageRecord->return_date = now();

        if ($usageRecord->return_qty >= $usageRecord->out_qty) {
            $usageRecord->status = 'returned';
        }
        else {
            $usageRecord->status = 'partial_return';
        }

        if (isset($validated['remarks'])) {
            $existing = $usageRecord->remarks ?? '';
            $usageRecord->remarks = trim($existing . "\n[Return]: " . $validated['remarks']);
        }

        $usageRecord->save();

        // Increment stock
        $item = $usageRecord->item;
        $item->quantity_in_stock += $validated['quantity'];
        $item->save();

        return response()->json($usageRecord);
    }
}