<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Business\BusinessInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class BusinessInventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = BusinessInventory::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('model', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('asset_tag', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->latest()->get());
    }

    public function store(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('Inventory Store Request', $request->except('image'));

        $validated = $request->validate([
            'asset_tag'     => 'nullable|string|unique:business_inventories,asset_tag',
            'category'      => 'required|string',
            'brand'         => 'required|string',
            'model'         => 'required|string',
            'serial_number' => 'required|string|unique:business_inventories,serial_number',
            'status'        => 'required|string',
            'quantity'      => 'required|integer|min:0',
            'image'         => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:5120',
            'purchase_date' => 'nullable|date',
            'cost'          => 'nullable|numeric',
            'remarks'       => 'nullable|string',
        ]);

        // Clean up empty strings to null for nullable fields
        foreach (['asset_tag', 'purchase_date', 'remarks', 'cost'] as $field) {
            if (isset($validated[$field]) && $validated[$field] === '') {
                $validated[$field] = null;
            }
        }

        unset($validated['image']);

        try {
            // Save the record first (without image)
            \Illuminate\Support\Facades\Log::info('Creating DB record...', $validated);
            $item = BusinessInventory::create($validated);
            \Illuminate\Support\Facades\Log::info('DB record created, id=' . $item->id);

            // Then try to upload image separately
            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                try {
                    \Illuminate\Support\Facades\Log::info('Storing image...');
                    $path = $request->file('image')->store('business-inventory', 'public');
                    $item->image_url = asset('storage/' . $path);
                    $item->save();
                    \Illuminate\Support\Facades\Log::info('Image stored successfully.');
                } catch (\Exception $imgEx) {
                    // Image upload failed but asset was already saved — just log and continue
                    \Illuminate\Support\Facades\Log::warning('Image upload failed (asset saved without image): ' . $imgEx->getMessage());
                }
            }

            return response()->json($item, 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Store failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to save asset: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        return response()->json(BusinessInventory::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $item = BusinessInventory::findOrFail($id);

        $validated = $request->validate([
            'asset_tag'     => 'nullable|string|unique:business_inventories,asset_tag,' . $id,
            'category'      => 'sometimes|required|string',
            'brand'         => 'sometimes|required|string',
            'model'         => 'sometimes|required|string',
            'serial_number' => 'sometimes|required|string|unique:business_inventories,serial_number,' . $id,
            'status'        => 'sometimes|required|string',
            'quantity'      => 'sometimes|required|integer|min:0',
            'image'         => 'nullable|image|max:4096',
            'purchase_date' => 'nullable|date',
            'cost'          => 'nullable|numeric',
            'remarks'       => 'nullable|string',
        ]);

        try {
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($item->image_url) {
                    $oldPath = str_replace(asset('storage/'), '', $item->image_url);
                    Storage::disk('public')->delete($oldPath);
                }
                $path = $request->file('image')->store('business-inventory', 'public');
                $validated['image_url'] = asset('storage/' . $path);
            }

            unset($validated['image']);

            // Clean up empty strings to null for nullable fields
            foreach (['asset_tag', 'purchase_date', 'remarks', 'cost'] as $field) {
                if (array_key_exists($field, $validated) && $validated[$field] === '') {
                    $validated[$field] = null;
                }
            }

            $item->update($validated);
            return response()->json($item);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update asset: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $item = BusinessInventory::findOrFail($id);

        if ($item->image_url) {
            $oldPath = str_replace(asset('storage/'), '', $item->image_url);
            Storage::disk('public')->delete($oldPath);
        }

        $item->delete();
        return response()->json(null, 204);
    }
}
