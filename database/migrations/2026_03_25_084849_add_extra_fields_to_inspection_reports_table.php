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
        Schema::table('inspection_reports', function (Blueprint $table) {
            $table->string('rfwi_ref_no')->nullable()->after('title');
            $table->string('location')->nullable()->after('rfwi_ref_no');
            $table->string('gridline_zone')->nullable()->after('location');
            $table->date('date_inspected')->nullable()->after('gridline_zone');
            $table->string('consultant_comments')->nullable()->after('date_inspected');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inspection_reports', function (Blueprint $table) {
            $table->dropColumn(['rfwi_ref_no', 'location', 'gridline_zone', 'date_inspected', 'consultant_comments']);
        });
    }
};
