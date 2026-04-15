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
        Schema::table('listing_photos', function (Blueprint $table) {
            $table->string('collection')->nullable()->after('thumbnail_path');
        });

        // Set default collection for existing photos
        \Illuminate\Support\Facades\DB::table('listing_photos')->update(['collection' => 'foto_fitur']);

        Schema::table('listing_photos', function (Blueprint $table) {
            $table->dropColumn('is_featured');
        });

        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listing_photos', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false);
            $table->dropColumn('collection');
        });

        Schema::table('listings', function (Blueprint $table) {
            $table->string('image')->nullable();
        });
    }
};
