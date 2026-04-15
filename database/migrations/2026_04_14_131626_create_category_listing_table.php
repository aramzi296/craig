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
        Schema::create('category_listing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('listing_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        // Migrate existing data
        $listings = \Illuminate\Support\Facades\DB::table('listings')->select('id', 'category_id')->get();
        foreach ($listings as $listing) {
            if ($listing->category_id) {
                // Check if the category still exists to avoid foreign key violations in the pivot
                $categoryExists = \Illuminate\Support\Facades\DB::table('categories')->where('id', $listing->category_id)->exists();
                if ($categoryExists) {
                    \Illuminate\Support\Facades\DB::table('category_listing')->insert([
                        'category_id' => $listing->category_id,
                        'listing_id' => $listing->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        /*
        if (config('database.default') === 'sqlite') {
            \Illuminate\Support\Facades\DB::statement('PRAGMA foreign_keys = OFF');
        }

        Schema::table('listings', function (Blueprint $table) {
            if (config('database.default') !== 'sqlite') {
                $table->dropForeign(['category_id']);
            }
            $table->dropColumn('category_id');
        });

        if (config('database.default') === 'sqlite') {
            \Illuminate\Support\Facades\DB::statement('PRAGMA foreign_keys = ON');
        }
        */
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->constrained();
        });

        // Optional: migrate back data if needed, but usually down is for rollback logic

        Schema::dropIfExists('category_listing');
    }
};
