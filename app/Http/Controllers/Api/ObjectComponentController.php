<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ObjectComponent;
use App\Models\ObjectStatus;
use App\Models\ObjectPort;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ObjectComponentController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'zone_id' => 'required|exists:zones,id',
            'system_type' => 'required|string',
        ]);

        /* Fetch objects for a given zone + system type with LATEST status */
        $objects = ObjectComponent::with(['latestStatus', 'ports.connectedToObject'])
            ->where('zone_id', $request->zone_id)
            ->where('system_type', $request->system_type)
            ->get();

        return response()->json($objects);
    }

    public function all(Request $request)
    {
        /* Fetch ALL objects across the system for listing/reporting tables */
        $objects = ObjectComponent::with(['latestStatus', 'ports.connectedToObject', 'zone.floor.building', 'user'])
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($objects);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'zone_id' => 'required|exists:zones,id',
            'item_alias_id' => 'nullable|string',
            'item_name' => 'required|string',
            'system_type' => 'required|string',
            'gridline_coords' => 'nullable|string',
            'cabling_type' => 'nullable|string',
            'pos_x' => 'required|numeric',
            'pos_y' => 'required|numeric',
            'rotation' => 'nullable|numeric',
            'status' => 'required|string', // Fix1, Fix2, etc.
            'status_image' => 'required_if:status,Pending|nullable|file|image|max:5120',
            'status_image_url' => 'nullable|url',
            'remarks' => 'nullable|string',
            'shape_type' => 'nullable|string',
            'geometry' => 'nullable|json',
        ]);

        if ($validator->fails()) {
            \Illuminate\Support\Facades\Log::error('Validation failed on Store Object: ' . json_encode($validator->errors()));
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $user_id = $request->user()->id ?? 1;

            $object = ObjectComponent::create([
                'project_id' => $request->header('X-Project-Id'),
                'zone_id' => $request->zone_id,
                'user_id' => $user_id,
                'item_alias_id' => $request->item_alias_id,
                'item_name' => $request->item_name,
                'system_type' => $request->system_type,
                'gridline_coords' => $request->gridline_coords,
                'cabling_type' => $request->cabling_type,
                'pos_x' => $request->pos_x,
                'pos_y' => $request->pos_y,
                'rotation' => $request->rotation ?? 0,
                'shape_type' => $request->shape_type ?? 'point',
                'geometry' => $request->geometry ? json_decode($request->geometry, true) : null,
            ]);

            $imageUrl = $request->status_image_url;
            if ($request->hasFile('status_image')) {
                $path = $request->file('status_image')->store('object_status_images', 'public');
                $imageUrl = url('storage/' . $path);
            }

            ObjectStatus::create([
                'object_id' => $object->id,
                'user_id' => $user_id,
                'current_status' => $request->status,
                'image_url' => $imageUrl,
                'remarks' => $request->remarks,
            ]);

            DB::commit();

            return response()->json(['message' => 'Object placed successfully', 'data' => $object->load('latestStatus')], 201);
        }
        catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create object: ' . $e->getMessage()], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string',
            'image' => 'required_if:status,Pending|nullable|file|image|max:5120',
            'image_url' => 'nullable|url',
            'remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $object = ObjectComponent::findOrFail($id);

        $imageUrl = $request->image_url;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('object_status_images', 'public');
            $imageUrl = url('storage/' . $path);
        }

        $status = ObjectStatus::create([
            'object_id' => $object->id,
            'user_id' => $request->user()->id ?? 1,
            'current_status' => $request->status,
            'image_url' => $imageUrl,
            'remarks' => $request->remarks,
        ]);

        return response()->json(['message' => 'Status updated successfully', 'data' => $status], 200);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rotation' => 'nullable|numeric',
            'pos_x' => 'nullable|numeric',
            'pos_y' => 'nullable|numeric',
            'item_alias_id' => 'nullable|string',
            'cabling_type' => 'nullable|string',
            'gridline_coords' => 'nullable|string',
            'shape_type' => 'nullable|string',
            'geometry' => 'nullable|json',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $object = ObjectComponent::findOrFail($id);

        if ($request->has('geometry')) {
            $validated = $validator->validated();
            $validated['geometry'] = json_decode($request->geometry, true);
            $object->fill($validated);
        }
        else {
            $object->fill($validator->validated());
        }

        $object->save();

        return response()->json(['message' => 'Object updated successfully', 'data' => $object], 200);
    }

    public function updatePort(Request $request, $id, $portId)
    {
        $validator = Validator::make($request->all(), [
            'port_name' => 'nullable|string',
            'status' => 'nullable|string',
            'cable_id' => 'nullable|string',
            'connected_to_object_id' => 'nullable|integer',
            'connected_port_name' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $object = ObjectComponent::findOrFail($id);
        $port = ObjectPort::where('object_component_id', $object->id)->findOrFail($portId);

        // Before updating, if we are changing connection, we should ideally handle the bi-directional cleanup
        // But for now, let's keep it simple as per user request for editing status and basic fields
        
        $port->update($request->only([
            'port_name', 'status', 'cable_id', 'connected_to_object_id', 'connected_port_name'
        ]));

        // Handle bi-directional status update if linked
        if ($port->connected_to_object_id && $port->connected_port_name) {
            $targetPort = ObjectPort::where('object_component_id', $port->connected_to_object_id)
                ->where('port_name', $port->connected_port_name)
                ->first();
            
            if ($targetPort) {
                $targetPort->update([
                    'status' => $port->status,
                    'cable_id' => $port->cable_id,
                    'connected_to_object_id' => $object->id,
                    'connected_port_name' => $port->port_name,
                ]);
            }
        }

        return response()->json(['message' => 'Port updated successfully', 'data' => $port], 200);
    }

    public function destroy($id)
    {
        $object = ObjectComponent::findOrFail($id);
        // Delete related statuses first, then the object
        $object->statuses()->delete();
        $object->ports()->delete();
        $object->delete();
        return response()->json(['message' => 'Object deleted successfully'], 200);
    }

    public function addPort(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'port_name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $object = ObjectComponent::findOrFail($id);

        $port = ObjectPort::create([
            'object_component_id' => $object->id,
            'port_name' => $request->port_name,
            'status' => 'online', // or 'offline' since no cable yet
        ]);

        return response()->json(['message' => 'Port created successfully', 'data' => $port], 201);
    }

    public function establishLink(Request $request, $id, $portId)
    {
        $validator = Validator::make($request->all(), [
            'cable_id' => 'required|string',
            'connected_to_object_id' => 'required|integer|exists:object_components,id',
            'connected_port_name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $object = ObjectComponent::findOrFail($id);
        $port = ObjectPort::where('object_component_id', $object->id)->findOrFail($portId);

        $port->update([
            'cable_id' => $request->cable_id,
            'connected_to_object_id' => $request->connected_to_object_id,
            'connected_port_name' => $request->connected_port_name,
            'status' => 'online',
        ]);

        // Bi-directional link: update the target port with the same Cable ID
        if ($request->connected_to_object_id && $request->connected_port_name) {
            $targetPort = ObjectPort::where('object_component_id', $request->connected_to_object_id)
                ->where('port_name', $request->connected_port_name)
                ->first();

            if ($targetPort) {
                $targetPort->update([
                    'cable_id' => $request->cable_id,
                    'connected_to_object_id' => $object->id,
                    'connected_port_name' => $port->port_name,
                    'status' => 'online',
                ]);
            }
        }

        return response()->json(['message' => 'Link established successfully', 'data' => $port], 200);
    }

    public function deletePort($id, $portId)
    {
        $object = ObjectComponent::findOrFail($id);
        $port = ObjectPort::where('object_component_id', $object->id)->findOrFail($portId);
        $port->delete();
        return response()->json(['message' => 'Port deleted successfully'], 200);
    }
}