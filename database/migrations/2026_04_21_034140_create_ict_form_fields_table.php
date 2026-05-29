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
        Schema::create('ict_form_fields', function (Blueprint $table) {
            $table->id();
            $table->string('field_key')->unique(); // e.g. 'backup_date'
            $table->string('label'); // e.g. 'Date of Backup'
            $table->string('type')->default('text'); // text, date, select, file, number
            $table->boolean('required')->default(false);
            $table->json('options')->nullable(); // For select types
            $table->string('section')->default('General');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ict_form_fields');
    }
};
