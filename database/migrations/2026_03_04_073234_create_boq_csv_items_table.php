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
        Schema::create('boq_csv_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('boq_csv_upload_id');
            $table->string('item_id');
            $table->string('alias_prefix')->nullable();
            $table->string('legend_dbn_name')->nullable();
            $table->string('status')->default('unassigned');
            $table->timestamps();

            $table->foreign('boq_csv_upload_id')->references('id')->on('boq_csv_uploads')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boq_csv_items');
    }
};
