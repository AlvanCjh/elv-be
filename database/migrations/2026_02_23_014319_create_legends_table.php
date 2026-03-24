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
        Schema::create('legends', function (Blueprint $table) {
            $table->id();
            $table->string('system_type', 50);
            $table->string('name');
            $table->string('shape_type', 50); // point, polyline, polygon
            $table->text('icon_svg')->nullable(); // SVG string for point
            $table->json('style')->nullable(); // styling properties
            $table->timestamps();
        });

        // Seed some BSS basic defaults based on user screenshot
        \Illuminate\Support\Facades\DB::table('legends')->insert([
            [
                'system_type' => 'bss',
                'name' => 'ABH B TRUNKING 75mm x 75mm',
                'shape_type' => 'polyline',
                'icon_svg' => null,
                'style' => json_encode(['strokeColor' => '#2ac7da', 'strokeWidth' => 2, 'strokeDasharray' => 'none']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'system_type' => 'bss',
                'name' => 'ABH B UPVC CONDUIT 20mm',
                'shape_type' => 'polyline',
                'icon_svg' => null,
                'style' => json_encode(['strokeColor' => '#2ac7da', 'strokeWidth' => 2, 'strokeDasharray' => '4,4']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'system_type' => 'bss',
                'name' => 'UPVC END BOX',
                'shape_type' => 'point',
                'icon_svg' => '<circle cx="0" cy="0" r="6" fill="white" stroke="red" stroke-width="2"/><line x1="6" y1="0" x2="10" y2="0" stroke="red" stroke-width="2"/>',
                'style' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'system_type' => 'bss',
                'name' => 'UPVC TWO WAY BOX',
                'shape_type' => 'point',
                'icon_svg' => '<circle cx="0" cy="0" r="6" fill="white" stroke="red" stroke-width="2"/><line x1="-10" y1="0" x2="-6" y2="0" stroke="red" stroke-width="2"/><line x1="6" y1="0" x2="10" y2="0" stroke="red" stroke-width="2"/>',
                'style' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'system_type' => 'bss',
                'name' => 'UPVC ELBOW BOX',
                'shape_type' => 'point',
                'icon_svg' => '<circle cx="0" cy="0" r="6" fill="white" stroke="red" stroke-width="2"/><line x1="0" y1="6" x2="0" y2="10" stroke="red" stroke-width="2"/><line x1="6" y1="0" x2="10" y2="0" stroke="red" stroke-width="2"/>',
                'style' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'system_type' => 'bss',
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
        Schema::dropIfExists('legends');
    }
};
