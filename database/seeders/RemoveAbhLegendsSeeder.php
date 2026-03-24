<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Legend;
use App\Models\ObjectComponent;

class RemoveAbhLegendsSeeder extends Seeder
{
    public function run()
    {
        $namesToDelete = ['ABH B TRUNKING 75mm x 75mm', 'ABH B UPVC CONDUIT 20mm'];
        
        // Remove objects instances first
        ObjectComponent::where('system_type', 'bss')
            ->whereIn('item_name', $namesToDelete)
            ->delete();

        // Delete the legends
        Legend::where('system_type', 'bss')->whereIn('name', $namesToDelete)->delete();
    }
}
