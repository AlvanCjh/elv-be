<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateDataFromPgToMysql extends Command
{
    protected $signature = 'db:migrate-data';
    protected $description = 'Migrate data from PostgreSQL to MySQL';

    public function handle()
    {
        $this->info('Starting data migration from PostgreSQL to MySQL...');
        
        $pgTables = DB::connection('pgsql')->select("SELECT table_name FROM information_schema.tables WHERE table_schema='public' AND table_type='BASE TABLE'");
        
        $tables = [];
        foreach ($pgTables as $pfTable) {
            $tableName = $pfTable->table_name;
            if ($tableName !== 'migrations' && $tableName !== 'floor_annotations' && $tableName !== 'cable_ports' && $tableName !== 'cable_connections') {
                $tables[] = $tableName;
            }
        }

        DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=0;');

        // Ensure tables are wiped
        foreach ($tables as $table) {
            try {
                DB::connection('mysql')->table($table)->truncate();
            } catch (\Exception $e) {
                // Table might not exist or be empty
            }
        }

        foreach ($tables as $table) {
            $this->info("Migrating table: {$table}");
            
            try {
                $records = DB::connection('pgsql')->table($table)->get();
                $count = $records->count();
                $this->info("Found {$count} records in {$table}");

                $chunks = $records->chunk(500);
                foreach ($chunks as $chunk) {
                    $insertData = [];
                    foreach ($chunk as $record) {
                        $arrayRecord = (array) $record;
                        $insertData[] = $arrayRecord;
                    }
                    if (!empty($insertData)) {
                        DB::connection('mysql')->table($table)->insert($insertData);
                    }
                }
                
                $this->info("Successfully migrated {$table}.");
                
            } catch (\Exception $e) {
                $this->error("Failed to migrate table {$table}. Error: " . $e->getMessage());
            }
        }
        
        DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info('Data migration completed!');
    }
}
