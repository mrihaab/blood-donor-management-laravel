<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->status !== 'active') {
                $userId = $user->id;
                $status = $user->status;

                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                Log::warning('Active account check failed: session invalidated', [
                    'user_id' => $userId,
                    'status' => $status,
                    'ip' => $request->ip(),
                ]);

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Your account is inactive or blocked.',
                    ], 403);
                }

                return redirect()->route('login')->withErrors([
                    'email' => 'Your account is inactive or blocked. Access denied.',
                ]);
            }
        }

        return $next($request);
    }
}
