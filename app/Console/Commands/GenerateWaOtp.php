<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class GenerateWaOtp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wa:otp {phone : The WhatsApp number of the user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and display a 6-digit OTP for a specific WhatsApp number (Admin Emergency Login)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $phone = User::normalizeWhatsappNumber($this->argument('phone'));
        $user = User::where('whatsapp', $phone)->first();

        if (!$user) {
            $this->error("User with number {$phone} not found.");
            return 1;
        }

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->update([
            'wa_otp1'            => Hash::make($otp),
            'wa_otp1_lookup'     => hash('sha256', $otp),
            'wa_otp1_expires_at' => now()->addMinutes(15),
        ]);

        $this->info("OTP created successfully for {$user->name} ({$phone})");
        $this->warn("OTP CODE: {$otp}");
        $this->info("Expires at: " . $user->wa_otp1_expires_at->toDateTimeString());

        return 0;
    }
}
