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
        Schema::table('boq_csv_items', function (Blueprint $table) {
            $table->string('floor_number')->nullable()->after('legend_dbn_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boq_csv_items', function (Blueprint $table) {
            $table->dropColumn('floor_number');
        });
    }
};