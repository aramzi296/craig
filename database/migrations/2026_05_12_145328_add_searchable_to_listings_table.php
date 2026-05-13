<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->text('searchable')->nullable();
        });

        // Add GIN index for full-text search if using PostgreSQL
        if (config('database.default') === 'pgsql') {
            DB::statement("CREATE INDEX listings_searchable_index ON listings USING GIN (to_tsvector('indonesian', searchable))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (config('database.default') === 'pgsql') {
            DB::statement("DROP INDEX IF EXISTS listings_searchable_index");
        }

        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn('searchable');
        });
    }
};
