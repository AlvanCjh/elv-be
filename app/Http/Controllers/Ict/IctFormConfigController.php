<?php

namespace App\Http\Controllers\Ict;

use App\Http\Controllers\Controller;
use App\Models\Ict\IctFormField;
use Illuminate\Http\Request;

class IctFormConfigController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->query('category', 'backup');
        return response()->json(IctFormField::where('category', $category)->orderBy('sort_order')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'nullable|string',
            'field_key' => 'required|string',
            'label' => 'required|string',
            'type' => 'required|string',
            'required' => 'boolean',
            'options' => 'nullable|array',
            'section' => 'string',
            'sort_order' => 'integer'
        ]);

        $category = $request->input('category', 'backup');
        $exists = IctFormField::where('field_key', $validated['field_key'])->where('category', $category)->exists();
        if ($exists) {
            return response()->json(['message' => 'Field key already exists for this category.'], 422);
        }

        $validated['category'] = $category;
        $field = IctFormField::create($validated);
        return response()->json($field, 201);
    }

    public function update(Request $request, $id)
    {
        $field = IctFormField::findOrFail($id);
        
        $validated = $request->validate([
            'category' => 'nullable|string',
            'field_key' => 'string',
            'label' => 'string',
            'type' => 'string',
            'required' => 'boolean',
            'options' => 'nullable|array',
            'section' => 'string',
            'sort_order' => 'integer',
            'is_active' => 'boolean'
        ]);

        if (isset($validated['field_key'])) {
            $category = $request->input('category', $field->category);
            $exists = IctFormField::where('field_key', $validated['field_key'])->where('category', $category)->where('id', '!=', $id)->exists();
            if ($exists) {
                return response()->json(['message' => 'Field key already exists for this category.'], 422);
            }
        }

        $field->update($validated);
        return response()->json($field);
    }

    public function destroy($id)
    {
        IctFormField::findOrFail($id)->delete();
        return response()->json(['message' => 'Field deleted']);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer',
            'items.*.sort_order' => 'required|integer'
        ]);

        foreach ($request->items as $item) {
            IctFormField::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['message' => 'Order updated']);
    }
}
