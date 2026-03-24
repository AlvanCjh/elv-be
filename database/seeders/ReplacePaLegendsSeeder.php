<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Legend;
use App\Models\ObjectComponent;

class ReplacePaLegendsSeeder extends Seeder
{
    public function run()
    {
        // 1. Delete the old generic PA UPVC point boxes and polyline ABH'S
        $namesToDelete = [
            'ABH B TRUNKING 75mm x 75mm',
            'ABH B UPVC CONDUIT CONCEALED 20mm',
            'UPVC END BOX',
            'UPVC TWO WAY BOX',
            'UPVC ELBOW BOX',
            'UPVC THREEWAY BOX'
        ];

        // Let's remap any existing objects using these old names over to BS1 to avoid breaking the map
        ObjectComponent::where('system_type', 'pa')
            ->whereIn('item_name', $namesToDelete)
            ->update(['item_name' => 'BS1']);

        Legend::where('system_type', 'pa')->whereIn('name', $namesToDelete)->delete();

        // 2. Define the new PA Legends
        $newLegends = [
            [
                'system_type' => 'pa',
                'name' => 'BS1', // Box Speaker
                'shape_type' => 'point',
                'icon_svg' => '<rect x="-12" y="-12" width="24" height="24" fill="white" stroke="#f59e0b" stroke-width="2"/><text x="0" y="4" font-family="Arial" font-size="9" font-weight="bold" fill="#f59e0b" text-anchor="middle">BS1</text>',
                'unit' => 'pcs',
                'unit_cost' => 120.00
            ],
            [
                'system_type' => 'pa',
                'name' => 'CS1', // Ceiling Speaker
                'shape_type' => 'point',
                'icon_svg' => '<circle cx="0" cy="0" r="14" fill="white" stroke="#d946ef" stroke-width="2"/><text x="0" y="4" font-family="Arial" font-size="9" font-weight="bold" fill="#d946ef" text-anchor="middle">CS1</text>',
                'unit' => 'pcs',
                'unit_cost' => 80.00
            ],
            [
                'system_type' => 'pa',
                'name' => 'HS1', // Horn Speaker
                'shape_type' => 'point',
                // Triangle pointing down
                'icon_svg' => '<polygon points="-14,-12 14,-12 0,14" fill="white" stroke="#3b82f6" stroke-width="2"/><text x="0" y="-1" font-family="Arial" font-size="8" font-weight="bold" fill="#3b82f6" text-anchor="middle">HS1</text>',
                'unit' => 'pcs',
                'unit_cost' => 250.00
            ],
            [
                'system_type' => 'pa',
                'name' => 'CS2', // 15W Coaxial Speaker
                'shape_type' => 'point',
                'icon_svg' => '<circle cx="0" cy="0" r="14" fill="white" stroke="#a855f7" stroke-width="2"/><text x="0" y="4" font-family="Arial" font-size="9" font-weight="bold" fill="#a855f7" text-anchor="middle">CS2</text>',
                'unit' => 'pcs',
                'unit_cost' => 100.00
            ]
        ];

        foreach ($newLegends as $legend) {
            Legend::updateOrCreate(
            ['system_type' => $legend['system_type'], 'name' => $legend['name']],
                $legend
            );
        }
    }
}