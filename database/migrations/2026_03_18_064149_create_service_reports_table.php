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
        Schema::create('service_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('service_report_no')->unique(); // R109/2026
            $table->string('company_name')->nullable();
            $table->text('address')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('telephone_no')->nullable();
            $table->string('taken_by')->nullable();
            $table->datetime('date_time')->nullable();
            $table->json('service_types'); // ['New installation', ...]
            $table->string('service_type_others_text')->nullable();
            $table->text('description')->nullable();
            $table->text('service_summary')->nullable(); // Can be a JSON list of steps
            $table->date('summary_date')->nullable();
            $table->time('summary_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_reports');
    }
};
