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
            $table->renameColumn('visibility_mode', 'whatsapp_visibility');
        });
        Schema::table('listings', function (Blueprint $table) {
            $table->tinyInteger('comment_visibility')->default(2)->comment('0: None, 1: Auth only, 2: All')->after('whatsapp_visibility');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn('comment_visibility');
        });
        Schema::table('listings', function (Blueprint $table) {
            $table->renameColumn('whatsapp_visibility', 'visibility_mode');
        });
    }
};
