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
        Schema::create('equipment_checksheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('equipment_name');
            $table->string('equipment_type'); // e.g., CCTV, UPS, Server, Panel
            $table->string('model_number')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('location')->nullable();
            
            // Technical Readings
            $table->string('voltage_v')->nullable();
            $table->string('current_a')->nullable();
            $table->string('other_readings')->nullable(); // For any other specific technical value
            
            // Detailed Checklist - stored as JSON
            $table->json('checklist_results')->nullable(); 
            
            $table->string('status')->default('pending'); // pending, pass, fail
            $table->string('checked_by')->nullable();
            $table->date('check_date')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_checksheets');
    }
};
