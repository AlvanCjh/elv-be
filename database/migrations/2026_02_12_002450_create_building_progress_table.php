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
        Schema::create('building_progress', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('material_id')->constrained()->cascadeOnDelete();
            $table->double('target_qty');
            $table->string('status')->default('in_progress');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('building_progress');
    }
};
