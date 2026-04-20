<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('updated_at');
        });

        // Update existing listings
        $listings = DB::table('listings')->get();
        $expireDays = config('sebatam.expire_iklan', 30);
        $expirePremiumDays = config('sebatam.expire_iklan_premium', 30);

        foreach ($listings as $listing) {
            $days = $listing->is_premium ? $expirePremiumDays : $expireDays;
            $createdAt = Carbon::parse($listing->created_at);
            
            DB::table('listings')
                ->where('id', $listing->id)
                ->update([
                    'expires_at' => $createdAt->addDays($days)
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn('expires_at');
        });
    }
};
