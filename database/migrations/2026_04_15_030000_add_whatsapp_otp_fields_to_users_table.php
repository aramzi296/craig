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
            // WhatsApp number (stored in international format, e.g. 62812xxxx)
            $table->string('whatsapp', 30)->nullable()->unique()->after('email');

            // Dual OTP for WhatsApp login
            $table->string('wa_otp1')->nullable()->after('whatsapp');        // hashed OTP #1
            $table->timestamp('wa_otp1_expires_at')->nullable()->after('wa_otp1');
            $table->string('wa_otp2')->nullable()->after('wa_otp1_expires_at'); // hashed OTP #2
            $table->timestamp('wa_otp2_expires_at')->nullable()->after('wa_otp2');

            // One-time login token generated after both OTPs verified
            $table->string('wa_login_token', 64)->nullable()->unique()->after('wa_otp2_expires_at');
            $table->timestamp('wa_login_token_expires_at')->nullable()->after('wa_login_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'whatsapp',
                'wa_otp1',
                'wa_otp1_expires_at',
                'wa_otp2',
                'wa_otp2_expires_at',
                'wa_login_token',
                'wa_login_token_expires_at',
            ]);
        });
    }
};
