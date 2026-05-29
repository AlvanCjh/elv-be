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
        Schema::create('ict_backups', function (Blueprint $table) {
            $table->id();
            $table->date('backup_date');
            $table->string('responsible_first_name');
            $table->string('responsible_last_name');
            $table->enum('department', ['ICT', 'ELV', 'SSDC', 'Others']);
            $table->json('systems'); // Stores an array of systems backed up
            $table->enum('status', ['success', 'unsuccess', 'recovery', 'in progress', 'failed']);
            $table->string('location');
            $table->string('attachment_url')->nullable();
            $table->unsignedBigInteger('project_id')->nullable(); // For multi-project support if needed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ict_backups');
    }
};
