<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TechnicalLayout;
use App\Models\TechnicalLayoutZone;
use App\Models\TechnicalLayoutZoneObject;
use App\Models\TechnicalLayoutZoneAnnotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class TechnicalLayoutController extends Controller
{
    public function index(Request $request)
    {
        $query = TechnicalLayout::query();
        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        return response()->json($query->with(['zones.objects', 'zones.annotations'])->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'name' => 'required|string',
            'image' => 'required|file|mimes:jpeg,png,jpg,svg|max:20240',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('layouts', $filename, 'public');
            
            $layout = TechnicalLayout::create([
                'project_id' => $request->project_id,
                'name' => $request->name,
                'image_path' => asset('storage/' . $path),
            ]);

            return response()->json($layout->load(['zones.objects', 'zones.annotations']), 201);
        }

        return response()->json(['message' => 'No image uploaded'], 400);
    }

    public function update(Request $request, $id)
    {
        $layout = TechnicalLayout::findOrFail($id);
        $request->validate([
            'name' => 'nullable|string',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,svg|max:20240',
        ]);

        if ($request->has('name')) {
            $layout->name = $request->name;
        }

        if ($request->hasFile('image')) {
            // Delete old file
            if ($layout->image_path) {
                if (str_contains($layout->image_path, 'storage/')) {
                    $oldPath = str_replace(asset('storage/'), '', $layout->image_path);
                    Storage::disk('public')->delete($oldPath);
                } else if (File::exists(public_path($layout->image_path))) {
                    File::delete(public_path($layout->image_path));
                }
            }

            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('layouts', $filename, 'public');
            $layout->image_path = asset('storage/' . $path);
        }

        $layout->save();
        return response()->json($layout->load(['zones.objects', 'zones.annotations']));
    }

    public function updateZones(Request $request, $id)
    {
        $layout = TechnicalLayout::findOrFail($id);

        $request->validate([
            'zones' => 'required|array',
            'zones.*.id' => 'nullable|integer',
            'zones.*.name' => 'required|string',
            'zones.*.svg_path' => 'required|string',
            'zones.*.color' => 'nullable|string',
            'zones.*.status' => 'nullable|string',
        ]);

        // Clear existing zones (and their objects/annotations if cascade is set, wait, cascade SHOULD be set in migrations)
        // Actually, deleting and recreating might be too destructive if they have objects/annotations.
        // Let's match by ID if exists, otherwise create.
        
        $zoneIds = [];
        foreach ($request->zones as $zData) {
            $match = [];
            if (isset($zData['id'])) {
                $match = ['id' => $zData['id']];
            } else {
                $match = ['name' => $zData['name'], 'svg_path' => $zData['svg_path']];
            }

            $zone = $layout->zones()->updateOrCreate(
                $match,
                [
                    'name' => $zData['name'],
                    'svg_path' => $zData['svg_path'],
                    'color' => $zData['color'] ?? '#6366f1', 
                    'status' => $zData['status'] ?? 'pending'
                ]
            );
            $zoneIds[] = $zone->id;
        }

        // Delete zones that are no longer present
        $layout->zones()->whereNotIn('id', $zoneIds)->delete();

        return response()->json($layout->load(['zones.objects', 'zones.annotations']));
    }

    public function destroy($id)
    {
        $layout = TechnicalLayout::findOrFail($id);
        
        // Delete image file
        if ($layout->image_path) {
            if (str_contains($layout->image_path, 'storage/')) {
                $oldPath = str_replace(asset('storage/'), '', $layout->image_path);
                Storage::disk('public')->delete($oldPath);
            } else if (File::exists(public_path($layout->image_path))) {
                File::delete(public_path($layout->image_path));
            }
        }

        $layout->delete();
        return response()->json(['message' => 'Layout deleted']);
    }

    public function addZoneObject(Request $request, $id)
    {
        $zone = TechnicalLayoutZone::findOrFail($id);
        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $object = $zone->objects()->create($request->only('name', 'description'));
        return response()->json($object, 201);
    }

    public function deleteZoneObject($id)
    {
        $object = TechnicalLayoutZoneObject::findOrFail($id);
        $object->delete();
        return response()->json(['message' => 'Object deleted']);
    }

    public function addZoneAnnotation(Request $request, $id)
    {
        $zone = TechnicalLayoutZone::findOrFail($id);
        $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'priority' => 'nullable|string|in:low,medium,high',
            'photo' => 'nullable|image|max:5120',
        ]);

        $data = $request->only('title', 'description', 'priority');
        
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('layouts/annotations', $filename, 'public');
            $data['photo_path'] = asset('storage/' . $path);
        }

        $annotation = $zone->annotations()->create($data);
        return response()->json($annotation, 201);
    }

    public function deleteZoneAnnotation($id)
    {
        $annotation = TechnicalLayoutZoneAnnotation::findOrFail($id);
        if ($annotation->photo_path) {
            if (str_contains($annotation->photo_path, 'storage/')) {
                $oldPath = str_replace(asset('storage/'), '', $annotation->photo_path);
                Storage::disk('public')->delete($oldPath);
            } else if (File::exists(public_path($annotation->photo_path))) {
                File::delete(public_path($annotation->photo_path));
            }
        }
        $annotation->delete();
        return response()->json(['message' => 'Annotation deleted']);
    }

    public function updateZoneStatus(Request $request, $id)
    {
        $zone = TechnicalLayoutZone::findOrFail($id);
        $request->validate(['status' => 'required|string']);
        $zone->update(['status' => $request->status]);
        return response()->json($zone);
    }

    public function deleteZone($id)
    {
        $zone = TechnicalLayoutZone::findOrFail($id);
        $zone->delete();
        return response()->json(['message' => 'Zone deleted']);
    }
}
