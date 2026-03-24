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
        Schema::create('maintenance_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('assigned_to_user_id'); // Site Engineer / Technician
            $table->string('created_by_user_id'); // Supervisor
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable(); // Image path
            $table->string('status')->default('pending'); // pending, completed
            $table->date('maintenance_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_reports');
    }
};
