<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds a custom_fields JSON column to store dynamic masterform field values.
     */
    public function up(): void
    {
        Schema::table('ict_backups', function (Blueprint $table) {
            $table->json('custom_fields')->nullable()->after('attachment_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ict_backups', function (Blueprint $table) {
            $table->dropColumn('custom_fields');
        });
    }
};
