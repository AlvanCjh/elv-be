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

        // -- Step 1: New GF-specific telco trunking legends --
        // These are different from the L5 ones (ABHB → TEL)
        $gfTrunkingLegends = [
            [
                'system_type' => 'telco',
                'name' => 'TEL TRUNKING 75mm x 75mm',
                'shape_type' => 'polyline',
                'icon_svg' => null,
                'style' => ['strokeColor' => '#7c3aed', 'strokeWidth' => 2, 'strokeDasharray' => 'none'],
            ],
            [
                'system_type' => 'telco',
                'name' => 'TEL UPVC CONDUIT CONCEALED 20mm',
                'shape_type' => 'polyline',
                'icon_svg' => null,
                'style' => ['strokeColor' => '#7c3aed', 'strokeWidth' => 2, 'strokeDasharray' => '6,4'],
            ],
        ];

        foreach ($gfTrunkingLegends as $data) {
            $legend = Legend::where('name', $data['name'])->where('system_type', $data['system_type'])->first();
            if (!$legend) {
                $legend = Legend::create($data);
            }
            $legend->floors()->syncWithoutDetaching([$floor_g->id]);
        }

        // -- Step 2: Attach the shared box legends (telco) to GF --
        // These already exist on L5, now also link them to G
        $sharedBoxNames = [
            'UPVC END BOX',
            'UPVC TWO WAY BOX',
            'UPVC ELBOW BOX',
            'UPVC THREE WAY BOX',
        ];

        foreach ($sharedBoxNames as $name) {
            $legend = Legend::where('name', $name)->where('system_type', 'telco')->first();
            if ($legend) {
                $legend->floors()->syncWithoutDetaching([$floor_g->id]);
            }
        }
    }

    public function down(): void
    {
        $floor_g = Floor::where('floor_number', 'G')->first();
        if (!$floor_g)
            return;

        $gfOnlyNames = [
            'TEL TRUNKING 75mm x 75mm',
            'TEL UPVC CONDUIT CONCEALED 20mm',
        ];

        foreach ($gfOnlyNames as $name) {
            $legend = Legend::where('name', $name)->where('system_type', 'telco')->first();
            if ($legend) {
                $legend->floors()->detach($floor_g->id);
            }
        }
    }
};
