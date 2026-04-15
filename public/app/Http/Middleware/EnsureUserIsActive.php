<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && !auth()->user()->is_active) {
            return redirect()->route('verify.whatsapp')
                ->with('warning', 'Verifikasi WhatsApp Anda terlebih dahulu sebelum membuat listing atau mengakses fitur ini.');
        }

        return $next($request);
    }
}
