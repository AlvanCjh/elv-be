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
        Schema::table('object_components', function (Blueprint $table) {
            $table->float('rotation')->default(0)->after('pos_y');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('object_components', function (Blueprint $table) {
            $table->dropColumn('rotation');
        });
    }
};
