<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Legend;
use App\Models\Floor;

return new class extends Migration {
    public function up(): void
    {
        // 1. Ensure 'Ground Floor' exists and locate it
        $floor_g = Floor::where('floor_number', 'G')->first();
        if (!$floor_g) {
            return; // Safety fallback
        }

        // 2. Define the exact legends from the picture specifically for Ground Floor
        $legendsData = [
            [
                'system_type' => 'bss',
                'name' => 'ABNB TRUNKING 100mm x 100mm',
                'shape_type' => 'polyline',
                'icon_svg' => null,
                'style' => ['strokeColor' => '#00ff00', 'strokeWidth' => 2, 'strokeDasharray' => 'none'],
            ],
            [
                'system_type' => 'bss',
                'name' => 'RETAIL TRUNKING 75mm x 75mm',
                'shape_type' => 'polyline',
                'icon_svg' => null,
                'style' => ['strokeColor' => '#ffff00', 'strokeWidth' => 2, 'strokeDasharray' => 'none'],
            ],
            [
                'system_type' => 'bss',
                'name' => 'ABNB UPVC CONDUIT CONCEALED 20mm',
                'shape_type' => 'polyline',
                'icon_svg' => null,
                'style' => ['strokeColor' => '#00ff00', 'strokeWidth' => 2, 'strokeDasharray' => '6,4'],
            ],
            [
                'system_type' => 'bss',
                'name' => 'RETAIL UPVC CONDUIT CONCEALED 20mm',
                'shape_type' => 'polyline',
                'icon_svg' => null,
                'style' => ['strokeColor' => '#ffff00', 'strokeWidth' => 2, 'strokeDasharray' => '6,4'],
            ],
            [
                'system_type' => 'bss',
                'name' => 'CAR PARK TRUNKING FOR GF PARKING 75mm x 75mm',
                'shape_type' => 'polyline',
                'icon_svg' => null,
                'style' => ['strokeColor' => '#00ffff', 'strokeWidth' => 2, 'strokeDasharray' => 'none'],
            ],
            [
                'system_type' => 'bss',
                'name' => 'FIBER TRUNKING 100mm x 100mm (400mm below UG slab soffit)',
                'shape_type' => 'polyline',
                'icon_svg' => null,
                'style' => ['strokeColor' => '#0000ff', 'strokeWidth' => 2, 'strokeDasharray' => 'none'],
            ],
            [
                'system_type' => 'bss',
                'name' => 'UPVC END BOX',
                'shape_type' => 'point',
                'icon_svg' => '<circle cx="0" cy="0" r="6" fill="white" stroke="red" stroke-width="2"/><line x1="6" y1="0" x2="10" y2="0" stroke="red" stroke-width="2"/>',
                'style' => null,
            ],
            [
                'system_type' => 'bss',
                'name' => 'UPVC TWO WAY BOX',
                'shape_type' => 'point',
                'icon_svg' => '<circle cx="0" cy="0" r="6" fill="white" stroke="red" stroke-width="2"/><line x1="-10" y1="0" x2="-6" y2="0" stroke="red" stroke-width="2"/><line x1="6" y1="0" x2="10" y2="0" stroke="red" stroke-width="2"/>',
                'style' => null,
            ],
            [
                'system_type' => 'bss',
                'name' => 'UPVC ELBOW BOX',
                'shape_type' => 'point',
                'icon_svg' => '<circle cx="0" cy="0" r="6" fill="white" stroke="red" stroke-width="2"/><line x1="0" y1="6" x2="0" y2="10" stroke="red" stroke-width="2"/><line x1="6" y1="0" x2="10" y2="0" stroke="red" stroke-width="2"/>',
                'style' => null,
            ],
            [
                'system_type' => 'bss',
                'name' => 'UPVC THREE WAY BOX',
                'shape_type' => 'point',
                'icon_svg' => '<circle cx="0" cy="0" r="6" fill="white" stroke="red" stroke-width="2"/><line x1="0" y1="6" x2="0" y2="10" stroke="red" stroke-width="2"/><line x1="6" y1="0" x2="10" y2="0" stroke="red" stroke-width="2"/><line x1="-10" y1="0" x2="-6" y2="0" stroke="red" stroke-width="2"/>',
                'style' => null,
            ]
        ];

        foreach ($legendsData as $data) {
            // Check if legend already exists exactly as named
            $legend = Legend::where('name', $data['name'])->where('system_type', $data['system_type'])->first();

            if (!$legend) {
                $legend = Legend::create($data);
            }

            // Sync with floor via pivot
            $legend->floors()->syncWithoutDetaching([$floor_g->id]);
        }
    }

    public function down(): void
    {
        // For safety, detach rather than delete raw legends so we don't break other floors matching names
        $floor_g = Floor::where('floor_number', 'G')->first();
        if ($floor_g) {
            $floor_g->legends()->detach();
        }
    }
};
