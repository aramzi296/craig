<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixPostgresSequences extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:fix-sequences';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fixes PostgreSQL auto-increment sequences that are out of sync';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            $this->warn('This command is only applicable for PostgreSQL databases.');
            return 0;
        }

        // Tulis semua nama tabel yang kira-kira butuh disinkronisasi sequence id-nya
        $tables = [
            'users',
            'categories',
            'districts',
            'subdistricts',
            'listings',
            'listing_reviews',
            'listing_reports',
            'listing_claims',
        ];

        foreach ($tables as $table) {
            try {
                $sequenceName = "{$table}_id_seq";
                DB::statement("SELECT setval('{$sequenceName}', (SELECT COALESCE(MAX(id), 1) FROM {$table}));");
                $this->info("✔ Sequence '{$sequenceName}' berhasil direset sesuai data terakhir tabel '{$table}'.");
            } catch (\Exception $e) {
                // Jika tabel tidak terstruktur menggunakan sequence bawaan, abaikan
            }
        }

        $this->info('PostgreSQL Sequences Synchronization Completed!');
        return 0;
    }
}
