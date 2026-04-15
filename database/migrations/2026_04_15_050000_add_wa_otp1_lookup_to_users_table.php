<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah kolom wa_otp1_lookup: SHA-256 dari OTP1 yang disimpan di DB
 * sehingga kita bisa mencari user berdasarkan OTP1 tanpa nonce di URL.
 * (Verifikasi tetap menggunakan bcrypt lewat wa_otp1)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('wa_otp1_lookup', 64)->nullable()->index()->after('wa_otp1');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('wa_otp1_lookup');
        });
    }
};
