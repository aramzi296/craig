<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegenerateWaVerificationController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->is_active || $user->whatsapp_verified_at) {
            return redirect()->route('dashboard');
        }

        $code = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        $user->update([
            'verification_code_hash' => Hash::make($code),
            'verification_code_expires_at' => now()->addHours(24),
            'wa_verify_failed_attempts' => 0,
        ]);

        $request->session()->flash('wa_verification_code_plain', $code);

        return redirect()->route('verify.whatsapp');
    }
}
