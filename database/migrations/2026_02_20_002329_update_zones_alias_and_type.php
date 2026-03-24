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
        // Update existing records: move name to alias_id, set name to 'Retail'
        DB::statement("UPDATE zones SET alias_id = name, name = 'Retail' WHERE alias_id IS NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore name from alias_id (lossy if name was changed to something else, but best effort)
        DB::statement("UPDATE zones SET name = alias_id WHERE alias_id IS NOT NULL");

        // Reset alias_id to null
        DB::statement("UPDATE zones SET alias_id = NULL");
    }
};
