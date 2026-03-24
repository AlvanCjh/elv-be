<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('usage_records', function (Blueprint $table) {
            $table->foreignId('building_progress_id')->nullable()->constrained('building_progress')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usage_records', function (Blueprint $table) {
            $table->dropForeign(['building_progress_id']);
            $table->dropColumn('building_progress_id');
        });
    }
};
