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
        // Seed some PA basic defaults based on user screenshot
        \Illuminate\Support\Facades\DB::table('legends')->insert([
            [
                'system_type' => 'pa',
                'name' => 'ABH B TRUNKING 75mm x 75mm',
                'shape_type' => 'polyline',
                'icon_svg' => null,
                'style' => json_encode(['strokeColor' => '#d946ef', 'strokeWidth' => 2, 'strokeDasharray' => 'none']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'system_type' => 'pa',
                'name' => 'ABH B UPVC CONDUIT CONCEALED 20mm',
                'shape_type' => 'polyline',
                'icon_svg' => null,
                'style' => json_encode(['strokeColor' => '#d946ef', 'strokeWidth' => 2, 'strokeDasharray' => '4,4']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'system_type' => 'pa',
                'name' => 'UPVC END BOX',
                'shape_type' => 'point',
                'icon_svg' => '<circle cx="0" cy="0" r="6" fill="white" stroke="red" stroke-width="2"/><line x1="6" y1="0" x2="10" y2="0" stroke="red" stroke-width="2"/>',
                'style' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'system_type' => 'pa',
                'name' => 'UPVC TWO WAY BOX',
                'shape_type' => 'point',
                'icon_svg' => '<circle cx="0" cy="0" r="6" fill="white" stroke="red" stroke-width="2"/><line x1="-10" y1="0" x2="-6" y2="0" stroke="red" stroke-width="2"/><line x1="6" y1="0" x2="10" y2="0" stroke="red" stroke-width="2"/>',
                'style' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'system_type' => 'pa',
                'name' => 'UPVC ELBOW BOX',
                'shape_type' => 'point',
                'icon_svg' => '<circle cx="0" cy="0" r="6" fill="white" stroke="red" stroke-width="2"/><line x1="0" y1="6" x2="0" y2="10" stroke="red" stroke-width="2"/><line x1="6" y1="0" x2="10" y2="0" stroke="red" stroke-width="2"/>',
                'style' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'system_type' => 'pa',
                'name' => 'UPVC THREEWAY BOX',
                'shape_type' => 'point',
                'icon_svg' => '<circle cx="0" cy="0" r="6" fill="white" stroke="red" stroke-width="2"/><line x1="0" y1="6" x2="0" y2="10" stroke="red" stroke-width="2"/><line x1="6" y1="0" x2="10" y2="0" stroke="red" stroke-width="2"/><line x1="-10" y1="0" x2="-6" y2="0" stroke="red" stroke-width="2"/>',
                'style' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::table('legends')->where('system_type', 'pa')->delete();
    }
};
