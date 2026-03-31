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
        Schema::create('risk_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('created_by_user_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->enum('type', ['fire', 'hazard', 'health', 'security', 'environmental', 'other'])->default('other');
            $table->enum('risk_level', ['low', 'medium', 'high', 'extreme'])->default('medium');
            $table->text('description')->nullable();
            $table->text('mitigation_plan')->nullable();
            $table->enum('status', ['open', 'in review', 'mitigated', 'closed'])->default('open');
            $table->date('assessment_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_assessments');
    }
};
