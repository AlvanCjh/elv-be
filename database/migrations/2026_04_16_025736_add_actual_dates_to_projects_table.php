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
        Schema::table('projects', function (Blueprint $table) {
            $table->date('actual_start_date')->nullable()->after('end_date');
            $table->date('actual_end_date')->nullable()->after('actual_start_date');
            $table->text('edit_reason')->nullable()->after('actual_end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['actual_start_date', 'actual_end_date', 'edit_reason']);
        });
    }
};
