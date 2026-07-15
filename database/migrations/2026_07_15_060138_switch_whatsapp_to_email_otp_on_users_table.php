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
            // Drop WhatsApp-related columns
            $table->dropColumn([
                'wa_otp1',
                'wa_otp1_expires_at',
                'wa_otp2',
                'wa_otp2_expires_at',
                'wa_login_token',
                'wa_login_token_expires_at',
                'wa_otp1_lookup', // this was added in another migration
            ]);

            // Add Email OTP columns
            $table->string('email_otp')->nullable()->after('password');
            $table->timestamp('email_otp_expires_at')->nullable()->after('email_otp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop Email OTP columns
            $table->dropColumn(['email_otp', 'email_otp_expires_at']);

            // Re-add WhatsApp-related columns
            $table->string('wa_otp1')->nullable()->after('whatsapp');
            $table->timestamp('wa_otp1_expires_at')->nullable()->after('wa_otp1');
            $table->string('wa_otp2')->nullable()->after('wa_otp1_expires_at');
            $table->timestamp('wa_otp2_expires_at')->nullable()->after('wa_otp2');
            $table->string('wa_login_token', 64)->nullable()->unique()->after('wa_otp2_expires_at');
            $table->timestamp('wa_login_token_expires_at')->nullable()->after('wa_login_token');
            $table->string('wa_otp1_lookup')->nullable()->after('wa_otp1');
        });
    }
};
