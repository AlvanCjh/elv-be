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
        Schema::table('ict_form_fields', function (Blueprint $table) {
            $table->string('category')->default('backup')->after('id');
            $table->dropUnique(['field_key']);
            $table->unique(['field_key', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ict_form_fields', function (Blueprint $table) {
            $table->dropUnique(['field_key', 'category']);
            $table->unique(['field_key']);
            $table->dropColumn('category');
        });
    }
};
