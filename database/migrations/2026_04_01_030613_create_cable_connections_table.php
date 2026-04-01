<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cable_connections', function (Blueprint $table) {
            $table->id();
            $table->string('cable_id')->unique();
            $table->string('cable_type')->default('Cat6');
            $table->foreignId('port_a_id')->constrained('cable_ports')->onDelete('cascade');
            $table->foreignId('port_b_id')->constrained('cable_ports')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cable_connections');
    }
};
