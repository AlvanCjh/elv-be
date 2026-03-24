<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FixSequenceSeeder extends Seeder {
    public function run(): void {
        $tables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_type = 'BASE TABLE'");
        
        foreach ($tables as $t) {
            $tableName = $t->table_name;
            try {
                if (DB::table($tableName)->count() > 0) {
                    $maxId = DB::table($tableName)->max('id');
                    if ($maxId) {
                        DB::statement("SELECT setval('{$tableName}_id_seq', {$maxId})");
                    }
                }
            } catch (\Exception $e) {}
        }
    }
}
