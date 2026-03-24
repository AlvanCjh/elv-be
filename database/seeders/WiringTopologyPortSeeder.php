<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ObjectPort;
use App\Models\ObjectComponent;

class WiringTopologyPortSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Target Riser ID 50 (RISER-L5-1)
        $riserId = 50;
        $riser = ObjectComponent::find($riserId);

        if (!$riser) {
            $this->command->error("Riser with ID 50 not found. Please check your database.");
            return;
        }

        $this->command->info("Seeding ports for Riser: " . $riser->item_alias_id);

        $prefixes = [
            'PP1' => 'RISL5-PP1-',
            'PP2' => 'RISL5-PP2-',
            'ES'  => 'RISL5-ES-',
            'ES2' => 'RISL5-ES2-',
        ];

        foreach ($prefixes as $key => $prefix) {
            for ($i = 1; $i <= 24; $i++) {
                $portName = $prefix . $i;

                // Create or update port
                ObjectPort::updateOrCreate(
                    [
                        'object_component_id' => $riserId,
                        'port_name' => $portName,
                    ],
                    [
                        'status' => 'online',
                    ]
                );
            }
        }

        $this->command->info("Successfully seeded 96 ports for Riser " . $riser->item_alias_id);
    }
}
