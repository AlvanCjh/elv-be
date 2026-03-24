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
        Schema::create('object_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->constrained('zones')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('item_alias_id')->nullable();
            $table->string('item_name');
            $table->string('gridline_coords')->nullable();
            $table->string('system_type')->nullable(); // BSS, PA, TELCO
            $table->string('cabling_type')->nullable();
            $table->float('conduit_length_m')->nullable();
            $table->float('trunking_length_m')->nullable();
            $table->float('cable_length_m')->nullable();
            $table->float('pos_x')->nullable();
            $table->float('pos_y')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('object_components');
    }
};
