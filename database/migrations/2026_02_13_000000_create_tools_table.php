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
        Schema::create('tools', function (Blueprint $table) {
            $table->id();
            $table->string('tool_name'); // Similar to material_name
            $table->text('description')->nullable();
            $table->string('brand')->nullable();
            $table->string('unit_of_measure')->default('units'); // Tools are usually counted in units
            $table->integer('quantity_in_stock')->default(0);
            $table->string('location')->nullable();
            $table->string('status')->default('available'); // e.g., available, in_use, broken
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tools');
    }
};
