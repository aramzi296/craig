<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateSqliteToPostgres extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:migrate-sqlite-to-postgres';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate data from SQLite database to PostgreSQL database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting data migration from SQLite to PostgreSQL...');

        // Verify connections
        try {
            DB::connection('sqlite')->getPdo();
            $this->info('Connected to SQLite.');
        } catch (\Exception $e) {
            $this->error('Failed to connect to SQLite: ' . $e->getMessage());
            return Command::FAILURE;
        }

        try {
            DB::connection('pgsql')->getPdo();
            $this->info('Connected to PostgreSQL.');
        } catch (\Exception $e) {
            $this->error('Failed to connect to PostgreSQL: ' . $e->getMessage());
            return Command::FAILURE;
        }

        // Get all tables from SQLite
        $tables = DB::connection('sqlite')->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
        
        // Disable foreign key checks in Postgres
        DB::connection('pgsql')->statement('SET session_replication_role = replica;');

        // Pass 1: Truncate all tables first to avoid TRUNCATE CASCADE wiping out already migrated data
        foreach ($tables as $tableInfo) {
            $table = $tableInfo->name;
            if ($table !== 'migrations') {
                DB::connection('pgsql')->table($table)->truncate();
            }
        }

        // Pass 2: Migrate data
        foreach ($tables as $tableInfo) {
            $table = $tableInfo->name;
            if ($table === 'migrations') {
                continue;
            }
            $this->info("Migrating table: {$table}");


            // Get boolean columns for this table from Postgres
            $booleanColumns = [];
            $columnsInfo = DB::connection('pgsql')->select("
                SELECT column_name
                FROM information_schema.columns
                WHERE table_name = ? AND data_type = 'boolean'
            ", [$table]);
            foreach ($columnsInfo as $col) {
                $booleanColumns[] = $col->column_name;
            }

            // Fetch data from SQLite
            $data = DB::connection('sqlite')->table($table)->get()->map(function ($item) use ($booleanColumns) {
                $arrayItem = (array) $item;
                // Cast integer 0/1 to boolean string for boolean columns
                foreach ($booleanColumns as $col) {
                    if (isset($arrayItem[$col]) || array_key_exists($col, $arrayItem)) {
                        $val = $arrayItem[$col];
                        if ($val === null) {
                            $arrayItem[$col] = null;
                        } else {
                            $arrayItem[$col] = $val ? 'true' : 'false';
                        }
                    }
                }
                return $arrayItem;
            })->toArray();

            if (count($data) > 0) {
                // Insert in chunks to avoid memory issues
                $chunks = array_chunk($data, 500);
                $bar = $this->output->createProgressBar(count($chunks));
                $bar->start();

                foreach ($chunks as $chunk) {
                    DB::connection('pgsql')->table($table)->insert($chunk);
                    $bar->advance();
                }

                $bar->finish();
                $this->newLine();
            } else {
                $this->line(" No data to migrate.");
            }
            
            // For tables with a sequence (auto-incrementing ID), we need to reset the sequence in Postgres
            if (Schema::connection('pgsql')->hasColumn($table, 'id')) {
                $maxId = DB::connection('pgsql')->table($table)->max('id');
                if ($maxId) {
                    $sequence = "{$table}_id_seq";
                    try {
                        DB::connection('pgsql')->statement("SELECT setval('{$sequence}', {$maxId})");
                    } catch (\Exception $e) {
                        // Sequence might not exist or be named differently, ignore
                        $this->line("   Notice: Could not reset sequence for {$table}: " . $e->getMessage());
                    }
                }
            }
        }

        // Re-enable foreign key checks in Postgres
        DB::connection('pgsql')->statement('SET session_replication_role = DEFAULT;');

        $this->info('Data migration completed successfully!');

        return Command::SUCCESS;
    }
}
