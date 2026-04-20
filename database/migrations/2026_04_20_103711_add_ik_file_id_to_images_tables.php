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
        Schema::table('users', function (Blueprint $table) {
            $table->string('ik_file_id')->nullable()->after('profile_photo');
        });

        Schema::table('listing_photos', function (Blueprint $table) {
            $table->string('ik_file_id')->nullable()->after('photo_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('ik_file_id');
        });

        Schema::table('listing_photos', function (Blueprint $table) {
            $table->dropColumn('ik_file_id');
        });
    }
};
