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
        Schema::table('listings', function (Blueprint $table) {
            $table->unsignedInteger('listing_rank')->default(1000)->after('is_active');
        });

        // Backfill existing data:
        // 1. Premium ads → rank 100
        \DB::statement("UPDATE listings SET listing_rank = 100 WHERE is_premium = true");

        // 2. Free ads → rank per-user (1000, 2000, 3000 ... ordered by created_at)
        $freeListings = \DB::table('listings')
            ->where('is_premium', false)
            ->orderBy('user_id')
            ->orderBy('created_at')
            ->get(['id', 'user_id']);

        $userCounter = [];
        foreach ($freeListings as $listing) {
            $userId = $listing->user_id;
            if (!isset($userCounter[$userId])) {
                $userCounter[$userId] = 0;
            }
            $userCounter[$userId]++;
            $rank = $userCounter[$userId] * 1000;

            \DB::table('listings')
                ->where('id', $listing->id)
                ->update(['listing_rank' => $rank]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn('listing_rank');
        });
    }
};
