<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Riser;
use App\Models\FloorAnnotation;
use App\Models\CablePort;
use App\Models\Legend;
use Illuminate\Http\Request;

class RiserController extends Controller
{
    public function index($floorId, Request $request)
    {
        $projectId = $request->query('project_id');
        
        // 1. Get explicit Riser records
        $risers = Riser::where('floor_id', $floorId)
            ->when($projectId, fn($q) => $q->where(fn($sub) => $sub->where('project_id', $projectId)->orWhereNull('project_id')))
            ->with(['racks.cablePorts'])
            ->get();
            
        // 2. Get "Riser Panel" annotations that aren't linked to a Riser record yet
        // Legend ID 13 is "Riser Panel"
        $rpLegendId = Legend::where('name', 'Riser Panel')->value('id') ?? 13;
        
        $rpAnnotations = FloorAnnotation::where('floor_id', $floorId)
            ->where('legend_id', $rpLegendId)
            ->when($projectId, fn($q) => $q->where(fn($sub) => $sub->where('project_id', $projectId)->orWhereNull('project_id')))
            ->whereNotIn('id', $risers->pluck('annotation_id')->filter())
            ->get();
            
        // 3. Merge them into a unified list
        $virtualRisers = $rpAnnotations->map(function($ann) {
            return [
                'id' => 'virtual_' . $ann->id,
                'annotation_id' => $ann->id,
                'floor_id' => $ann->floor_id,
                'project_id' => $ann->project_id,
                'name' => $ann->name ?: ('RISER-' . ($ann->zone ? $ann->zone->alias_id : 'RP') . '-' . $ann->id),
                'location' => $ann->description,
                'racks' => [],
                'is_virtual' => true
            ];
        });

        return response()->json($risers->concat($virtualRisers));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'floor_id' => 'required|exists:floors,id',
            'name' => 'required|string',
            'location' => 'nullable|string',
            'annotation_id' => 'nullable|exists:floor_annotations,id'
        ]);

        $riser = Riser::create($validated);
        return response()->json($riser, 201);
    }

    /**
     * Initialize the 4 standard racks for a riser.
     * 2 Patch Panels, 2 Ethernet Switches.
     * Each with 24 ports.
     */
    public function initializeRacks(Request $request, $id)
    {
        // Check if it's a virtual ID (annotation_id)
        if (strpos($id, 'virtual_') === 0) {
            $annotationId = (int) str_replace('virtual_', '', $id);
            $ann = FloorAnnotation::findOrFail($annotationId);
            
            // Create the real Riser first
            $riser = Riser::create([
                'project_id' => $ann->project_id,
                'floor_id' => $ann->floor_id,
                'annotation_id' => $ann->id,
                'name' => $ann->name ?: ('RISER-' . ($ann->zone ? $ann->zone->alias_id : 'RP') . '-' . $ann->id),
                'location' => $ann->description,
            ]);
        } else {
            $riser = Riser::findOrFail($id);
        }
        
        $patchLegend = Legend::firstOrCreate(
            ['name' => 'Patch Panel', 'system_type' => 'bss'],
            ['shape_type' => 'point', 'icon_svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="6" width="20" height="12" rx="2" /><path d="M6 12h.01M10 12h.01M14 12h.01M18 12h.01" /></svg>']
        );
        $ethernetLegend = Legend::firstOrCreate(
            ['name' => 'Ethernet Switch', 'system_type' => 'bss'],
            ['shape_type' => 'point', 'icon_svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2" /><path d="M6 8h.01M10 8h.01M14 8h.01M18 8h.01M6 16h.01M10 16h.01M14 16h.01M18 16h.01" /></svg>']
        );

        $racks = [
            ['name' => 'PATCH PANEL 1', 'legend_id' => $patchLegend->id],
            ['name' => 'PATCH PANEL 2', 'legend_id' => $patchLegend->id],
            ['name' => 'ETHERNET SWITCH 1', 'legend_id' => $ethernetLegend->id],
            ['name' => 'ETHERNET SWITCH 2', 'legend_id' => $ethernetLegend->id],
        ];

        $createdRacks = [];

        foreach ($racks as $rackData) {
            $rack = FloorAnnotation::create([
                'project_id' => $riser->project_id,
                'floor_id' => $riser->floor_id,
                'riser_id' => $riser->id,
                'legend_id' => $rackData['legend_id'],
                'name' => $rackData['name'],
                'category' => 'BSS',
                'status' => 'complete',
                'coordinates' => ['x' => 0, 'y' => 0],
            ]);

            for ($i = 1; $i <= 24; $i++) {
                CablePort::create([
                    'annotation_id' => $rack->id,
                    'port_number' => str_pad($i, 2, '0', STR_PAD_LEFT),
                    'status' => 'active'
                ]);
            }
            
            $createdRacks[] = $rack->load('cablePorts');
        }

        return response()->json([
            'message' => 'Riser initialized with 4 racks and 96 ports.',
            'racks' => $createdRacks
        ]);
    }
}
