<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Legend;
use App\Models\ObjectComponent;

class ReplaceBssLegendsSeeder extends Seeder
{
    public function run()
    {
        // 1. Delete the old generic BSS UPVC point boxes
        $namesToDelete = ['UPVC END BOX', 'UPVC TWO WAY BOX', 'UPVC ELBOW BOX', 'UPVC THREEWAY BOX'];

        // Before deleting, let's remap any existing objects using these old names on the map over to CAM3
        ObjectComponent::where('system_type', 'bss')
            ->whereIn('item_name', $namesToDelete)
            ->update(['item_name' => 'CAM3']);

        Legend::where('system_type', 'bss')->whereIn('name', $namesToDelete)->delete();

        // 2. Define the new BSS Legends with specific text-based SVGs
        $newLegends = [
            [
                'system_type' => 'bss',
                'name' => 'CAM1',
                'shape_type' => 'point',
                'icon_svg' => '<circle cx="0" cy="0" r="14" fill="white" stroke="#3b82f6" stroke-width="2"/><text x="0" y="4" font-family="Arial" font-size="8" font-weight="bold" fill="#3b82f6" text-anchor="middle">CAM1</text>',
                'unit' => 'pcs',
                'unit_cost' => 150.00
            ],
            [
                'system_type' => 'bss',
                'name' => 'CAM3',
                'shape_type' => 'point',
                'icon_svg' => '<circle cx="0" cy="0" r="14" fill="white" stroke="#3b82f6" stroke-width="2"/><text x="0" y="4" font-family="Arial" font-size="8" font-weight="bold" fill="#3b82f6" text-anchor="middle">CAM3</text>',
                'unit' => 'pcs',
                'unit_cost' => 200.00
            ],
            [
                'system_type' => 'bss',
                'name' => 'R1',
                'shape_type' => 'point',
                'icon_svg' => '<rect x="-12" y="-12" width="24" height="24" fill="white" stroke="#10b981" stroke-width="2"/><text x="0" y="4" font-family="Arial" font-size="9" font-weight="bold" fill="#10b981" text-anchor="middle">R1</text>',
                'unit' => 'pcs',
                'unit_cost' => 85.00
            ],
            [
                'system_type' => 'bss',
                'name' => 'R2',
                'shape_type' => 'point',
                'icon_svg' => '<rect x="-12" y="-12" width="24" height="24" fill="white" stroke="#10b981" stroke-width="2"/><text x="0" y="4" font-family="Arial" font-size="9" font-weight="bold" fill="#10b981" text-anchor="middle">R2</text>',
                'unit' => 'pcs',
                'unit_cost' => 95.00
            ],
            [
                'system_type' => 'bss',
                'name' => 'R3',
                'shape_type' => 'point',
                'icon_svg' => '<rect x="-12" y="-12" width="24" height="24" fill="white" stroke="#10b981" stroke-width="2"/><text x="0" y="4" font-family="Arial" font-size="9" font-weight="bold" fill="#10b981" text-anchor="middle">R3</text>',
                'unit' => 'pcs',
                'unit_cost' => 105.00
            ],
            [
                'system_type' => 'bss',
                'name' => 'HP',
                'shape_type' => 'point',
                'icon_svg' => '<rect x="-12" y="-12" width="24" height="24" fill="white" stroke="#ef4444" stroke-width="2"/><text x="0" y="4" font-family="Arial" font-size="9" font-weight="bold" fill="#ef4444" text-anchor="middle">HP</text>',
                'unit' => 'pcs',
                'unit_cost' => 300.00
            ],
            [
                'system_type' => 'bss',
                'name' => 'CT',
                'shape_type' => 'point',
                'icon_svg' => '<circle cx="0" cy="0" r="12" fill="white" stroke="#8b5cf6" stroke-width="2"/><text x="0" y="4" font-family="Arial" font-size="9" font-weight="bold" fill="#8b5cf6" text-anchor="middle">CT</text>',
                'unit' => 'pcs',
                'unit_cost' => 45.00
            ],
            [
                'system_type' => 'bss',
                'name' => 'R4',
                'shape_type' => 'point',
                'icon_svg' => '<rect x="-12" y="-12" width="24" height="24" fill="white" stroke="#10b981" stroke-width="2"/><text x="0" y="4" font-family="Arial" font-size="9" font-weight="bold" fill="#10b981" text-anchor="middle">R4</text>',
                'unit' => 'pcs',
                'unit_cost' => 110.00
            ],
            [
                'system_type' => 'bss',
                'name' => 'GT',
                'shape_type' => 'point',
                'icon_svg' => '<circle cx="0" cy="0" r="14" fill="white" stroke="#6b7280" stroke-width="2"/><text x="0" y="4" font-family="Arial" font-size="9" font-weight="bold" fill="#6b7280" text-anchor="middle">GT</text>',
                'unit' => 'pcs',
                'unit_cost' => 50.00
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