<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Legend;
use App\Models\Floor;

return new class extends Migration {
    public function up(): void
    {
        $floor_l5 = Floor::where('floor_number', 'L5')->first();
        if (!$floor_l5) {
            return;
        }

        // Attach all previous legends (e.g. telco, pa, cctv) to L5
        $previousLegends = Legend::where('system_type', '!=', 'bss')->get();
        foreach ($previousLegends as $legend) {
            $legend->floors()->syncWithoutDetaching([$floor_l5->id]);
        }
    }

    public function down(): void
    {
        $floor_l5 = Floor::where('floor_number', 'L5')->first();
        if ($floor_l5) {
            $telcoLegendIds = Legend::where('system_type', 'telco')->pluck('id')->toArray();
            $floor_l5->legends()->detach($telcoLegendIds);
        }
    }
};
