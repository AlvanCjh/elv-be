<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FloorAnnotation;
use App\Models\Legend;
use App\Models\PendingHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FloorAnnotationController extends Controller
{
    private function withPhotoUrl(FloorAnnotation $ann): FloorAnnotation
    {
        $ann->photo_url = $ann->photo_path ? Storage::url($ann->photo_path) : null;
        return $ann;
    }

    // GET /annotations?floor_id=9 OR /floors/{floorId}/annotations
    public function index(Request $request, $floorId = null)
    {
        $id = $floorId ?? $request->floor_id;
        
        if (!$id) {
            return response()->json(['error' => 'floor_id is required'], 400);
        }

        $query = FloorAnnotation::with('legend')
            ->withCount(['cablePorts as has_port_problem' => function ($q) {
                $q->where('status', 'problem');
            }])
            ->where('floor_id', $id);

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('category')) $query->where('category', $request->category);
        if ($request->filled('zone_id'))  $query->where('zone_id', $request->zone_id);

        return response()->json(
            $query->orderBy('created_at')->get()->map(fn(FloorAnnotation $a) => $this->withPhotoUrl($a))
        );
    }

    // POST /floors/{floorId}/annotations
    public function store(Request $request, $floorId)
    {
        $data = $request->validate([
            'zone_id'       => 'nullable|exists:zones,id',
            'category'      => 'required|in:BSS,TEL,PAM',
            'legend_id'     => 'nullable|exists:legends,id',
            'name'          => 'nullable|string|max:200',
            'description'   => 'nullable|string',
            'coordinates'   => 'required|array|min:1',
            'coordinates.*.x' => 'required|numeric|min:0|max:100',
            'coordinates.*.y' => 'required|numeric|min:0|max:100',
            'status'        => 'required|in:fix1,fix2,fix3,pending,complete,ongoing,additional_routing,adjustments',
            'remarks'       => 'nullable|string',
            'not_our_fault' => 'nullable|boolean',
            'rotation'      => 'nullable|integer|min:0|max:359',
            'photo'         => 'sometimes|file|image|max:10240',
            'project_id'    => 'required|exists:projects,id',
            'due_date'      => 'nullable|date',
        ]);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('annotation-photos', 'public');
        }
        unset($data['photo']);

        $ann = FloorAnnotation::create(array_merge($data, ['floor_id' => $floorId]));
        $ann->load('legend', 'zone');

        PendingHistory::create([
            'floor_annotation_id' => $ann->id,
            'name' => $ann->name ?? 'New Annotation',
            'date' => now()->toDateString(),
            'base_location' => $ann->zone ? ($ann->zone->alias_id ?? $ann->zone->name) : 'Floor',
            'status' => $ann->status,
            'remarks' => $ann->remarks,
            'updated_by_user_id' => Auth::id(),
        ]);

        return response()->json($this->withPhotoUrl($ann), 201);
    }

    // POST /annotations/{id}  (supports multipart for photo upload)
    public function update(Request $request, $id)
    {
        $ann = FloorAnnotation::findOrFail($id);

        $data = $request->validate([
            'zone_id'       => 'sometimes|nullable|exists:zones,id',
            'legend_id'     => 'sometimes|nullable|exists:legends,id',
            'name'          => 'sometimes|nullable|string|max:200',
            'description'   => 'sometimes|nullable|string',
            'coordinates'   => 'sometimes|array|min:1',
            'coordinates.*.x' => 'sometimes|numeric|min:0|max:100',
            'coordinates.*.y' => 'sometimes|numeric|min:0|max:100',
            'status'        => 'sometimes|in:fix1,fix2,fix3,pending,complete,ongoing,additional_routing,adjustments',
            'remarks'       => 'sometimes|nullable|string',
            'not_our_fault' => 'sometimes|nullable|boolean',
            'rotation'      => 'sometimes|integer|min:0|max:359',
            'photo'         => 'sometimes|file|image|max:10240',
            'due_date'      => 'sometimes|nullable|date',
        ]);

        if ($request->hasFile('photo')) {
            if ($ann->photo_path) Storage::disk('public')->delete($ann->photo_path);
            $data['photo_path'] = $request->file('photo')->store('annotation-photos', 'public');
        }
        unset($data['photo']);

        $oldStatus = $ann->status;
        $ann->update($data);
        
        $ann->load('legend', 'zone');
        if ($oldStatus !== $ann->status) {
            PendingHistory::create([
                'floor_annotation_id' => $ann->id,
                'name' => $ann->name ?? 'Annotation Update',
                'date' => now()->toDateString(),
                'base_location' => $ann->zone ? ($ann->zone->alias_id ?? $ann->zone->name) : 'Floor',
                'status' => $ann->status,
                'remarks' => $ann->remarks,
                'updated_by_user_id' => Auth::id(),
            ]);
        }

        return response()->json($this->withPhotoUrl($ann));
    }

    // DELETE /annotations/{id}
    public function destroy($id)
    {
        $ann = FloorAnnotation::findOrFail($id);
        if ($ann->photo_path) Storage::disk('public')->delete($ann->photo_path);
        $ann->delete();
        return response()->json(null, 204);
    }

    /**
     * Bulk delete annotations for a floor and category.
     */
    public function bulkDestroy(Request $request, $floorId)
    {
        $request->validate([
            'category' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $query = FloorAnnotation::where('floor_id', $floorId)
                ->where('category', $request->category);
            
            if ($request->filled('project_id')) {
                $query->where('project_id', $request->project_id);
            }
            
            $count = $query->count();
            
            // Delete photos if any
            $anns = $query->get();
            foreach ($anns as $ann) {
                if ($ann->photo_path) Storage::disk('public')->delete($ann->photo_path);
            }

            $query->delete();

            DB::commit();
            return response()->json(['message' => "Successfully removed $count annotations."]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Bulk Delete error: " . $e->getMessage());
            return response()->json(['message' => 'Failed to delete annotations.'], 500);
        }
    }
}
