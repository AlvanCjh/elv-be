<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Legend;
use App\Models\ObjectComponent;

class ReplaceTelcoLegendsSeeder extends Seeder
{
    public function run()
    {
        // 1. Delete the old generic TELCO UPVC point boxes and polyline ABH'S
        $namesToDelete = [
            'ABHB TRUNKING 75mm x 75mm',
            'ABHB UPVC CONDUIT 20mm',
            'UPVC END BOX',
            'UPVC TWO WAY BOX',
            'UPVC ELBOW BOX',
            'UPVC THREE WAY BOX' // Note: Telco had it spaced as "THREE WAY" 
        ];

        // Let's remap any existing objects using these old names over to FWS to avoid breaking the map
        ObjectComponent::where('system_type', 'telco')
            ->whereIn('item_name', $namesToDelete)
            ->update(['item_name' => 'FWS']);

        Legend::where('system_type', 'telco')->whereIn('name', $namesToDelete)->delete();

        // 2. Define the new TELCO Legends
        $newLegends = [
            [
                'system_type' => 'telco',
                'name' => 'FWS', // Fiber Wall Socket
                'shape_type' => 'point',
                'icon_svg' => '<rect x="-14" y="-8" width="28" height="16" fill="white" stroke="#2563eb" stroke-width="2"/><text x="0" y="3" font-family="Arial" font-size="8" font-weight="bold" fill="#2563eb" text-anchor="middle">FWS</text>',
                'unit' => 'pcs',
                'unit_cost' => 65.00
            ],
            [
                'system_type' => 'telco',
                'name' => 'FTB', // Fibre Termination Board
                'shape_type' => 'point',
                'icon_svg' => '<rect x="-14" y="-8" width="28" height="16" fill="white" stroke="#eab308" stroke-width="2"/><text x="0" y="3" font-family="Arial" font-size="8" font-weight="bold" fill="#eab308" text-anchor="middle">FTB</text>',
                'unit' => 'pcs',
                'unit_cost' => 450.00
            ],
            [
                'system_type' => 'telco',
                'name' => 'FWS2', // 2-Core Fibre Wall Socket
                'shape_type' => 'point',
                'icon_svg' => '<rect x="-14" y="-8" width="28" height="16" fill="white" stroke="#2563eb" stroke-width="2"/><text x="0" y="3" font-family="Arial" font-size="7" font-weight="bold" fill="#2563eb" text-anchor="middle">FWS2</text>',
                'unit' => 'pcs',
                'unit_cost' => 60.00
            ],
            [
                'system_type' => 'telco',
                'name' => 'FWS4', // 4-Core Fibre Wall Socket
                'shape_type' => 'point',
                'icon_svg' => '<rect x="-14" y="-8" width="28" height="16" fill="white" stroke="#2563eb" stroke-width="2"/><text x="0" y="3" font-family="Arial" font-size="7" font-weight="bold" fill="#2563eb" text-anchor="middle">FWS4</text>',
                'unit' => 'pcs',
                'unit_cost' => 95.00
            ],
            [
                'system_type' => 'telco',
                'name' => 'DATA', // Data Point
                'shape_type' => 'point',
                'icon_svg' => '<rect x="-12" y="-12" width="24" height="24" fill="white" stroke="#3b82f6" stroke-width="2"/><circle cx="0" cy="0" r="4" fill="#3b82f6" /><text x="0" y="16" font-family="Arial" font-size="6" font-weight="bold" fill="#3b82f6" text-anchor="middle">DATA</text>',
                'unit' => 'pcs',
                'unit_cost' => 40.00
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