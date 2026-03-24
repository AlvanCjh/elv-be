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
        Schema::table('object_components', function (Blueprint $table) {
            $table->json('geometry')->nullable();
            $table->string('shape_type')->default('point');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('object_components', function (Blueprint $table) {
            $table->dropColumn(['geometry', 'shape_type']);
        });
    }
};