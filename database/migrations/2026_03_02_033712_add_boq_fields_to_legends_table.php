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
        Schema::table('legends', function (Blueprint $table) {
            $table->string('unit', 20)->default('pcs')->after('shape_type');
            $table->decimal('unit_cost', 10, 2)->default(0.00)->after('unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('legends', function (Blueprint $table) {
            $table->dropColumn(['unit', 'unit_cost']);
        });
    }
};