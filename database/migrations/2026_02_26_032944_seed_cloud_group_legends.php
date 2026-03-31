<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $systems = ['bss', 'pa', 'telco'];
        $iconSvg = '<path d="M 15 35 Q 15 25 25 25 Q 30 15 45 20 Q 55 15 65 25 Q 75 25 75 35 Q 85 40 75 50 Q 75 60 65 60 L 25 60 Q 15 60 15 50 Q 5 45 15 35 Z" fill="rgba(59, 130, 246, 0.4)" stroke="#3b82f6" stroke-width="3" transform="translate(-5, -10) scale(0.65)"/>';

        foreach ($systems as $system) {
            $legend = \App\Models\Legend::create([
                'system_type' => $system,
                'name' => 'CLOUD GROUP',
                'shape_type' => 'cloud',
                'icon_svg' => $iconSvg,
                'style' => [
                    'fillColor' => 'rgba(59, 130, 246, 0.2)',
                    'strokeColor' => '#3b82f6',
                    'strokeWidth' => 2,
                ],
            ]);

            // attach to all floors
            $floors = \App\Models\Floor::all();
            foreach ($floors as $floor) {
                $legend->floors()->attach($floor->id);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \App\Models\Legend::where('name', 'CLOUD GROUP')->delete();
    }
};
