<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Legend;
use App\Models\Floor;

return new class extends Migration {
    public function up(): void
    {
        $floor_g = Floor::where('floor_number', 'G')->first();
        $floor_l5 = Floor::where('floor_number', 'L5')->first();

        if (!$floor_g)
            return;

        // -- Step 1: New GF-specific PA trunking legends (not on L5) --
        $gfLegends = [
            [
                'system_type' => 'pa',
                'name' => 'ABNB TRUNKING 100mm x 100mm',
                'shape_type' => 'polyline',
                'icon_svg' => null,
                'style' => ['strokeColor' => '#ef4444', 'strokeWidth' => 2, 'strokeDasharray' => 'none'],
            ],
            [
                'system_type' => 'pa',
                'name' => 'RETAIL TRUNKING 75mm x 75mm',
                'shape_type' => 'polyline',
                'icon_svg' => null,
                'style' => ['strokeColor' => '#ec4899', 'strokeWidth' => 2, 'strokeDasharray' => 'none'],
            ],
            [
                'system_type' => 'pa',
                'name' => 'ABNB UPVC CONDUIT CONCEALED 20mm',
                'shape_type' => 'polyline',
                'icon_svg' => null,
                'style' => ['strokeColor' => '#ef4444', 'strokeWidth' => 2, 'strokeDasharray' => '6,4'],
            ],
            [
                'system_type' => 'pa',
                'name' => 'RETAIL UPVC CONDUIT CONCEALED 20mm',
                'shape_type' => 'polyline',
                'icon_svg' => null,
                'style' => ['strokeColor' => '#ec4899', 'strokeWidth' => 2, 'strokeDasharray' => '6,4'],
            ],
            [
                'system_type' => 'pa',
                'name' => 'CAR PARK TRUNKING 75mm x 75mm (For GF Parking)',
                'shape_type' => 'polyline',
                'icon_svg' => null,
                'style' => ['strokeColor' => '#ca8a04', 'strokeWidth' => 2, 'strokeDasharray' => 'none'],
            ],
        ];

        foreach ($gfLegends as $data) {
            $legend = Legend::where('name', $data['name'])->where('system_type', $data['system_type'])->first();
            if (!$legend) {
                $legend = Legend::create($data);
            }
            $legend->floors()->syncWithoutDetaching([$floor_g->id]);
        }

        // -- Step 2: Attach existing PA box legends to L5 (they were never linked to any floor) --
        $l5PaLegends = [
            'ABH B TRUNKING 75mm x 75mm',
            'ABH B UPVC CONDUIT CONCEALED 20mm',
        ];
        if ($floor_l5) {
            foreach ($l5PaLegends as $name) {
                $legend = Legend::where('name', $name)->where('system_type', 'pa')->first();
                if ($legend) {
                    $legend->floors()->syncWithoutDetaching([$floor_l5->id]);
                }
            }
        }

        // -- Step 3: Attach shared PA box legends to BOTH G and L5 --
        $sharedBoxNames = [
            'UPVC END BOX',
            'UPVC TWO WAY BOX',
            'UPVC ELBOW BOX',
            'UPVC THREEWAY BOX',
        ];

        $floorIds = array_filter([$floor_g->id, $floor_l5?->id]);
        foreach ($sharedBoxNames as $name) {
            $legend = Legend::where('name', $name)->where('system_type', 'pa')->first();
            if ($legend) {
                $legend->floors()->syncWithoutDetaching($floorIds);
            }
        }
    }

    public function down(): void
    {
        $floor_g = Floor::where('floor_number', 'G')->first();
        if (!$floor_g)
            return;

        // Detach GF-specific PA trunking legends
        $gfOnlyNames = [
            'ABNB TRUNKING 100mm x 100mm',
            'RETAIL TRUNKING 75mm x 75mm',
            'ABNB UPVC CONDUIT CONCEALED 20mm',
            'RETAIL UPVC CONDUIT CONCEALED 20mm',
            'CAR PARK TRUNKING 75mm x 75mm (For GF Parking)',
        ];

        foreach ($gfOnlyNames as $name) {
            $legend = Legend::where('name', $name)->where('system_type', 'pa')->first();
            if ($legend) {
                $legend->floors()->detach($floor_g->id);
            }
        }
    }
};
