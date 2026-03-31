<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CableConnection;
use App\Models\CablePort;
use App\Models\FloorAnnotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CablePortController extends Controller
{
    /**
     * List all ports + their connections for a specific annotation.
     */
    public function index($annotationId)
    {
        $ports = CablePort::where('annotation_id', $annotationId)
            ->orderBy('port_number')
            ->get();

        $result = $ports->map(function (CablePort $port) {
            // A port can be a Sink (Port A) for a camera AND a Source (Port B) for a server/uplink
            $conns = CableConnection::where('port_a_id', $port->id)
                ->orWhere('port_b_id', $port->id)
                ->with(['portA.annotation', 'portB.annotation'])
                ->get();

            $fromConnection = null;
            $toConnection = null;

            foreach ($conns as $conn) {
                // If the port is port_a_id (Sink), it's receiving from port_b_id (Source)
                if ($conn->port_a_id === $port->id) {
                    $other = $conn->portB;
                    $fromConnection = [
                        'cable_id'               => $conn->cable_id,
                        'cable_type'             => $conn->cable_type,
                        'connection_id'          => $conn->id,
                        'port_id'                => $other->id,
                        'port_number'            => $other->port_number,
                        'status'                 => $other->status,
                        'annotation_id'          => $other->annotation->id,
                        'annotation_name'        => $other->annotation->name,
                        'annotation_coordinates' => $other->annotation->coordinates,
                        'annotation_floor_id'    => $other->annotation->floor_id,
                    ];
                } 
                // If the port is port_b_id (Source), it's sending to port_a_id (Sink)
                else {
                    $other = $conn->portA;
                    $toConnection = [
                        'cable_id'               => $conn->cable_id,
                        'cable_type'             => $conn->cable_type,
                        'connection_id'          => $conn->id,
                        'port_id'                => $other->id,
                        'port_number'            => $other->port_number,
                        'status'                 => $other->status,
                        'annotation_id'          => $other->annotation->id,
                        'annotation_name'        => $other->annotation->name,
                        'annotation_coordinates' => $other->annotation->coordinates,
                        'annotation_floor_id'    => $other->annotation->floor_id,
                    ];
                }
            }

            return array_merge($port->toArray(), [
                'from_connection' => $fromConnection,
                'to_connection'   => $toConnection,
                // Legacy support if needed
                'connected_to'    => $fromConnection ?? $toConnection
            ]);
        });

        return response()->json($result);
    }

    /**
     * Add a new port slot to a device.
     */
    public function store(Request $request, $annotationId)
    {
        $request->validate([
            'port_number' => 'required|integer|min:1',
            'status'      => 'string',
            'notes'       => 'nullable|string',
        ]);

        // Check for duplicate port number ON THIS DEVICE
        $exists = CablePort::where('annotation_id', $annotationId)
            ->where('port_number', $request->port_number)
            ->exists();

        if ($exists) {
            return response()->json(['message' => "Port number {$request->port_number} already exists on this device."], 422);
        }

        $port = CablePort::create([
            'annotation_id' => $annotationId,
            'port_number'   => $request->port_number,
            'status'        => $request->status ?? 'active',
            'notes'         => $request->notes,
        ]);

        return response()->json($port, 201);
    }

    /**
     * Update port status or notes.
     */
    public function update(Request $request, $portId)
    {
        $port = CablePort::findOrFail($portId);
        $port->update($request->only(['status', 'notes']));
        return response()->json($port);
    }

    /**
     * Delete a port (and its connection).
     */
    public function destroy($portId)
    {
        $port = CablePort::findOrFail($portId);
        $port->delete(); // Cascading delete in migration handles cable_connections
        return response()->json(['message' => 'Port deleted successfully.']);
    }

    /**
     * Link two ports with a specific cable_id (Upstream -> Downstream).
     */
    public function connect(Request $request)
    {
        $request->validate([
            'cable_id'   => 'required|string|unique:cable_connections,cable_id',
            'cable_type' => 'string',
            'port_a_id'  => 'required|exists:cable_ports,id', // Sink (e.g. Server/Patch end)
            'port_b_id'  => 'required|exists:cable_ports,id', // Source (e.g. Camera/Patch end)
        ]);

        // Ensure ports aren't already connected to something else
        $aInUse = CableConnection::where('port_a_id', $request->port_a_id)->orWhere('port_b_id', $request->port_a_id)->exists();
        $bInUse = CableConnection::where('port_b_id', $request->port_b_id)->orWhere('port_a_id', $request->port_b_id)->exists();

        if ($aInUse || $bInUse) {
            return response()->json(['message' => 'One or both ports are already connected to another cable.'], 422);
        }

        $connection = CableConnection::create([
            'cable_id'   => $request->cable_id,
            'cable_type' => $request->cable_type ?? 'Cat6',
            'port_a_id'  => $request->port_a_id,
            'port_b_id'  => $request->port_b_id,
        ]);

        return response()->json($connection, 201);
    }

    /**
     * Unlink two ports.
     */
    public function disconnect($connectionId)
    {
        $conn = CableConnection::findOrFail($connectionId);
        $conn->delete();
        return response()->json(['message' => 'Connection removed successfully.']);
    }

    /**
     * Update an existing connection (e.g. change cable_id).
     */
    public function updateConnection(Request $request, $connectionId)
    {
        $conn = CableConnection::findOrFail($connectionId);

        $request->validate([
            'cable_id'   => 'string|unique:cable_connections,cable_id,' . $conn->id,
            'cable_type' => 'string',
        ]);

        $conn->update($request->only(['cable_id', 'cable_type']));

        return response()->json($conn);
    }

    /**
     * Suggest the next cable ID for a floor.
     */
    public function suggestCableId(Request $request)
    {
        $floorId = $request->query('floor');
        if (!$floorId) return response()->json(['message' => 'Floor ID required.'], 400);

        // Find floor number for prefix, e.g. "L5"
        $floorRecord = DB::table('floors')->where('id', $floorId)->first();
        if (!$floorRecord) return response()->json(['message' => 'Floor not found.'], 404);

        $prefix = preg_replace('/[^0-9]/', '', $floorRecord->floor_number) ?: $floorRecord->floor_number;
        $prefix = "L" . $prefix . "-Cat6-";

        $lastCable = CableConnection::where('cable_id', 'LIKE', $prefix . '%')
            ->orderByRaw("CAST(SUBSTRING(cable_id, LENGTH('$prefix') + 1) AS UNSIGNED) DESC")
            ->first();

        $nextNum = 1;
        if ($lastCable) {
            $lastNum = (int) str_replace($prefix, '', $lastCable->cable_id);
            $nextNum = $lastNum + 1;
        }

        return response()->json([
            'suggested_id' => $prefix . str_pad($nextNum, 3, '0', STR_PAD_LEFT)
        ]);
    }

    /**
     * Look up a connection by cable ID.
     */
    public function showCable($cableId)
    {
        $conn = CableConnection::where('cable_id', $cableId)
            ->with(['portA.annotation', 'portB.annotation'])
            ->firstOrFail();

        return response()->json($conn);
    }

    /**
     * List all cable connections on a specific floor.
     * Joins through cable_ports -> floor_annotations -> floors.
     */
    public function cablesByFloor($floorId)
    {
        $connections = CableConnection::with(['portA.annotation.legend', 'portB.annotation.legend'])
            ->whereHas('portA.annotation', fn ($q) => $q->where('floor_id', $floorId))
            ->orWhereHas('portB.annotation', fn ($q) => $q->where('floor_id', $floorId))
            ->get();

        $result = $connections->map(function (CableConnection $conn) {
            $annA = $conn->portA?->annotation;
            $annB = $conn->portB?->annotation;
            return [
                'id'            => $conn->id,
                'cable_id'      => $conn->cable_id,
                'cable_type'    => $conn->cable_type,
                // port_a = Sink (the downstream device receiving)
                'sink'   => $annA ? [
                    'annotation_id'   => $annA->id,
                    'annotation_name' => $annA->name,
                    'legend_name'     => $annA->legend?->name,
                    'port_number'     => $conn->portA->port_number,
                ] : null,
                // port_b = Source (the upstream device sending)
                'source' => $annB ? [
                    'annotation_id'   => $annB->id,
                    'annotation_name' => $annB->name,
                    'legend_name'     => $annB->legend?->name,
                    'port_number'     => $conn->portB->port_number,
                ] : null,
            ];
        });

        return response()->json($result);
    }
}
