<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (get_setting('is_maintenance') === '1') {
            // Allow admin users to still access the site
            if (auth()->check() && auth()->user()->is_admin) {
                return $next($request);
            }

            // Also allow the login page so admins can login if they aren't
            if ($request->is('login') || $request->is('wa-login') || $request->is('admin*') || $request->is('logout')) {
                 return $next($request);
            }
            
            // Allow webhook for the bot (bot will handle its own maintenance check)
            if ($request->is('webhook/*')) {
                return $next($request);
            }

            if (!$request->is('maintenance')) {
                return redirect()->route('maintenance');
            }
        }

        return $next($request);
    }
}
