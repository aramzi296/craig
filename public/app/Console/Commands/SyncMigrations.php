<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Utility command: tandai semua migration yang belum tercatat sebagai sudah dijalankan.
 * Berguna saat database sudah ada (misal dari import) tapi migration records belum sinkron.
 * Hapus command ini setelah selesai dipakai.
 */
class SyncMigrations extends Command
{
    protected $signature = 'migrate:sync-records';
    protected $description = 'Mark all pending migrations as ran without executing them (useful for existing databases)';

    public function handle(): int
    {
        $pendingMigrations = [
            '2024_01_01_000001_create_geography_tables',
            '2024_01_01_000002_create_listing_types_table',
            '2024_01_01_000003_create_categories_table',
            '2024_01_01_000004_create_listings_table',
            '2024_01_01_000005_create_listing_reviews_table',
            '2024_01_01_000006_create_category_listing_pivot_table',
            '2024_01_01_000007_create_reports_and_claims_tables',
            '2024_01_01_000008_create_premium_and_settings_tables',
            '2024_01_01_000009_create_support_and_contact_tables',
            '2024_01_01_000010_create_integration_and_media_tables',
            '2024_01_01_000015_create_listings_search_table',
            '2024_01_01_000015_create_postgresql_fts_index',
            '2026_04_06_150807_remove_unused_columns_from_listings',
        ];

        // Ambil batch tertinggi saat ini
        $maxBatch = DB::table('migrations')->max('batch') ?? 0;
        $newBatch = $maxBatch + 1;

        $existing = DB::table('migrations')->pluck('migration')->toArray();

        $inserted = 0;
        foreach ($pendingMigrations as $migration) {
            if (!in_array($migration, $existing)) {
                DB::table('migrations')->insert([
                    'migration' => $migration,
                    'batch'     => $newBatch,
                ]);
                $this->line("  <info>✓</info> Marked as run: {$migration}");
                $inserted++;
            } else {
                $this->line("  <comment>–</comment> Already recorded: {$migration}");
            }
        }

        $this->newLine();
        $this->info("Done! {$inserted} migration(s) marked as ran (batch {$newBatch}).");

        return self::SUCCESS;
    }
}
