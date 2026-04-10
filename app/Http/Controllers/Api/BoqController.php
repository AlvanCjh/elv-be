<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BoqController extends Controller
{
    /**
     * Get aggregated Bill of Quantity Summary.
     * Joins drawn objects with their respective legend definitions to calculate count and cost.
     */
    public function getSummary(Request $request)
    {
        $query = DB::table('object_components')
            ->join('legends', function ($join) {
            $join->on('object_components.item_name', '=', 'legends.name')
                ->on('object_components.system_type', '=', 'legends.system_type');
        });

        // Explicitly enforce project scoping since raw DB queries bypass Eloquent Global Scopes
        if ($projectId = request()->header('X-Project-Id')) {
            $query->where('object_components.project_id', $projectId);
        }

        if ($request->has('floor_id')) {
            // Join zones to filter by floor_id
            $query->join('zones', 'object_components.zone_id', '=', 'zones.id');
            $query->where('zones.floor_id', $request->input('floor_id'));
        }

        // Subquery to get the latest status for each object
        $latestStatuses = DB::table('object_statuses')
            ->select('object_id', 'current_status')
            ->whereIn('id', function ($query) {
            $query->select(DB::raw('MAX(id)'))
                ->from('object_statuses')
                ->groupBy('object_id');
        });

        $query->leftJoinSub($latestStatuses, 'latest_status', function ($join) {
            $join->on('object_components.id', '=', 'latest_status.object_id');
        });

        $boqData = $query->select(
            'legends.system_type',
            'object_components.item_name',
            'legends.shape_type',
            'legends.unit',
            'legends.unit_cost',
            DB::raw('COUNT(object_components.id) as total_qty'),
            DB::raw("CAST(SUM(CASE WHEN latest_status.current_status IN ('Completed', 'Approved') THEN 1 ELSE 0 END) AS SIGNED) as completed_qty"),
            DB::raw("CAST(SUM(CASE WHEN latest_status.current_status = 'Finish' THEN 1 ELSE 0 END) AS SIGNED) as finished_qty"),
            DB::raw("CAST(SUM(CASE WHEN latest_status.current_status NOT IN ('Completed', 'Approved', 'Finish') OR latest_status.current_status IS NULL THEN 1 ELSE 0 END) AS SIGNED) as pending_qty"),
            DB::raw('COUNT(object_components.id) * legends.unit_cost as total_cost'),
            DB::raw("GROUP_CONCAT(object_components.item_alias_id SEPARATOR ', ') as alias_ids")
        )
            ->groupBy(
            'legends.system_type',
            'object_components.item_name',
            'legends.shape_type',
            'legends.unit',
            'legends.unit_cost'
        )
            ->orderBy('legends.system_type')
            ->orderBy('object_components.item_name')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $boqData
        ]);
    }

    /**
     * Get aggregated Cable Topology data for the active floor.
     * Fetches all ports that have a cable_id assigned and their linked targets.
     */
    public function getCableTopology(Request $request)
    {
        $request->validate([
            'floor_id' => 'required|integer'
        ]);

        $ports = \App\Models\ObjectPort::with([
            'objectComponent:id,item_alias_id,item_name,system_type',
            'connectedToObject:id,item_alias_id,item_name'
        ])
            ->whereNotNull('cable_id')
            ->whereHas('objectComponent.zone', function ($query) use ($request) {
            $query->where('floor_id', $request->input('floor_id'));
        });

        if ($request->has('system_type') && $request->input('system_type') !== 'All Types') {
            $ports->whereHas('objectComponent', function ($query) use ($request) {
                $query->where('system_type', $request->input('system_type'));
            });
        }

        $portsData = $ports->get();

        return response()->json([
            'status' => 'success',
            'data' => $portsData
        ]);
    }
}
