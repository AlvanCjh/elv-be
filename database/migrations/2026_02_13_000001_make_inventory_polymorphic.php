<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add polymorphic columns to usage_records
        Schema::table('usage_records', function (Blueprint $table) {
            $table->unsignedBigInteger('material_id')->nullable()->change(); // Make nullable
            $table->unsignedBigInteger('item_id')->nullable()->after('id');
            $table->string('item_type')->nullable()->after('item_id');
        });

        // Migrate existing data (assume all are materials)
        DB::table('usage_records')->whereNull('item_id')->update([
            'item_id' => DB::raw('material_id'),
            'item_type' => 'App\Models\Material'
        ]);

        // Add polymorphic columns to stock_in_records
        Schema::table('stock_in_records', function (Blueprint $table) {
            $table->unsignedBigInteger('material_id')->nullable()->change(); // Make nullable
            $table->unsignedBigInteger('item_id')->nullable()->after('id');
            $table->string('item_type')->nullable()->after('item_id');
        });

        // Migrate existing data
        DB::table('stock_in_records')->whereNull('item_id')->update([
            'item_id' => DB::raw('material_id'),
            'item_type' => 'App\Models\Material'
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usage_records', function (Blueprint $table) {
            $table->dropColumn(['item_id', 'item_type']);
            $table->unsignedBigInteger('material_id')->nullable(false)->change();
        });

        Schema::table('stock_in_records', function (Blueprint $table) {
            $table->dropColumn(['item_id', 'item_type']);
            $table->unsignedBigInteger('material_id')->nullable(false)->change();
        });
    }
};
