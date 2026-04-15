<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * @param  string  $role  Pipe-separated role names (same convention as spatie/laravel-permission).
     */
    public function handle(Request $request, Closure $next, string $role, ?string $guard = null): Response
    {
        $authGuard = $guard !== null ? Auth::guard($guard) : Auth::guard();
        $user = $authGuard->user();

        if (! $user) {
            abort(403);
        }

        $allowed = explode('|', $role);

        if (! in_array($user->role, $allowed, true)) {
            abort(403);
        }

        return $next($request);
    }
}
