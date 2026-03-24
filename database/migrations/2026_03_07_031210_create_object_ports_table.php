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
        Schema::create('object_ports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('object_component_id')->constrained()->onDelete('cascade');
            $table->string('port_name'); // e.g., 'CCTV PORT', 'MIXER PORT'
            $table->string('cable_id')->nullable(); // e.g., 'CAT5-L5-1'
            $table->foreignId('connected_to_object_id')->nullable()->constrained('object_components')->onDelete('set null');
            $table->string('status')->default('online'); // online, offline, pending
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('object_ports');
    }
};