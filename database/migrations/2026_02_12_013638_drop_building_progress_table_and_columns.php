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
        Schema::table('usage_records', function (Blueprint $table) {
            $table->dropForeign(['building_progress_id']);
            $table->dropColumn('building_progress_id');
        });

        Schema::dropIfExists('building_progress');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('building_progress', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('material_id')->constrained('materials')->onDelete('cascade');
            $table->integer('target_qty');
            $table->enum('status', ['in_progress', 'completed'])->default('in_progress');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::table('usage_records', function (Blueprint $table) {
            $table->foreignId('building_progress_id')->nullable()->constrained('building_progress')->onDelete('set null');
        });
    }
};
