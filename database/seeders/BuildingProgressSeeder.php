<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Zone;

class BuildingProgressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if building exists to avoid duplicates
        if (Building::where('name', 'The Peninsula')->exists()) {
            return;
        }

        $building = Building::create([
            'name' => 'The Peninsula',
            'total_floor' => 30,
            'latitude' => 3.1390,
            'longitude' => 101.6869,
        ]);

        // Create Floors
        $floors = [];
        for ($i = 1; $i <= 30; $i++) {
            $floors[] = [
                'floor_number' => "L$i",
                'type' => $i < 5 ? 'Commercial' : 'Residential',
                'floor_plan_svg' => "assets/maps/floor_plans/L$i.svg",
            ];
        }

        $building->floors()->createMany($floors);

        // Focus on Floor 5
        $floor5 = $building->floors()->where('floor_number', 'L5')->first();
        if ($floor5) {
            // Update with a more specific placeholder if needed, or keep the loop's value
            $floor5->update(['floor_plan_svg' => 'assets/maps/floor_plans/L5_zoning.png']);

            // Create Zones for L5 based on image
            $zones = [
                ['name' => 'L5 S01', 'area' => '304 m2', 'location_desc' => 'Central View'],
                ['name' => 'L5 S02', 'area' => '375 m2', 'location_desc' => 'Sea View'],
                ['name' => 'L5 S03', 'area' => '300 m2', 'location_desc' => 'Garden View'],
                ['name' => 'L5 S04', 'area' => '384 m2', 'location_desc' => 'Corner Unit'],
                ['name' => 'L5 S05', 'area' => '262 m2', 'location_desc' => 'Standard Unit'],
            ];
            $floor5->zones()->createMany($zones);
        }
    }
}
