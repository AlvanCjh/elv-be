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
        Schema::create('daily_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('company_type'); // kinetic_motion, sabahnet
            $table->date('check_date');
            $table->string('attendee_name')->nullable();
            $table->string('verified_by')->nullable();
            $table->string('status_summary')->default('Normal');
            $table->json('sections_data'); // Stores all readings and checkboxes
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_checklists');
    }
};
