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
        Schema::table('object_ports', function (Blueprint $table) {
            $table->string('connected_port_name')->nullable()->after('connected_to_object_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('object_ports', function (Blueprint $table) {
            $table->dropColumn('connected_port_name');
        });
    }
};
