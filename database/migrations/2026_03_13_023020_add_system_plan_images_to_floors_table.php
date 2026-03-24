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
        Schema::table('floors', function (Blueprint $table) {
            $table->string('bss_plan_image_url')->nullable()->after('floor_plan_image_url');
            $table->string('pa_plan_image_url')->nullable()->after('bss_plan_image_url');
            $table->string('telco_plan_image_url')->nullable()->after('pa_plan_image_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('floors', function (Blueprint $table) {
            $table->dropColumn(['bss_plan_image_url', 'pa_plan_image_url', 'telco_plan_image_url']);
        });
    }
};
