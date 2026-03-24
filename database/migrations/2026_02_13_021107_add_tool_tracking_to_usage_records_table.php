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
            $table->date('expected_return_date')->nullable();
            $table->date('return_date')->nullable();
            $table->integer('return_qty')->nullable()->default(0);
            $table->string('status')->nullable(); // e.g., 'borrowed', 'returned', 'overdue'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usage_records', function (Blueprint $table) {
            $table->dropColumn(['expected_return_date', 'return_date', 'return_qty', 'status']);
        });
    }
};
