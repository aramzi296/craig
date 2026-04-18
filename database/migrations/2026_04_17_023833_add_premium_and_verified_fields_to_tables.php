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
            $table->boolean('is_premium')->default(false)->after('is_featured');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_verified')->default(false)->after('password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn('is_premium');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_verified');
        });
    }

};
