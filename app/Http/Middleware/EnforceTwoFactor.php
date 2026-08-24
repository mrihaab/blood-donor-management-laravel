<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceTwoFactor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isAdmin() && $user->google2fa_enabled) {
            if ($request->routeIs('admin.2fa.*') || $request->routeIs('logout')) {
                return $next($request);
            }

            if (!session('2fa_verified')) {
                return redirect()->route('admin.2fa.show')
                    ->with('warning', 'Please verify 2FA to access administrative controls.');
            }
        }

        return $next($request);
    }
}
