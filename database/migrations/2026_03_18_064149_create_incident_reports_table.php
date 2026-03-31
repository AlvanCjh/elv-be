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
        Schema::dropIfExists('incident_reports');
        Schema::create('incident_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('ir_no')->unique(); // KMSB/IR/2026/0001
            $table->string('reported_by');
            $table->date('report_date');
            $table->string('role_of_recorded')->default('CUSTOMER SERVICE ENGINEER');
            $table->json('incident_types'); // ['EMSB', 'HSSD', ...]
            $table->string('incident_type_others_text')->nullable();
            $table->string('affected_equipment')->nullable();
            $table->string('incident_location')->nullable();
            $table->date('finding_date')->nullable();
            $table->string('incident_scenario')->nullable(); // MINOR, MAJOR, etc.
            $table->text('incident_description')->nullable();
            $table->text('specifications')->nullable();
            $table->text('inability')->nullable();
            $table->text('impact')->nullable();
            $table->text('operation')->nullable();
            $table->text('recommendations')->nullable();
            $table->text('replacement_capability')->nullable();
            $table->text('remarks')->nullable();
            $table->string('verified_by')->nullable();
            $table->string('verified_designation')->nullable();
            $table->date('verified_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incident_reports');
    }
};
