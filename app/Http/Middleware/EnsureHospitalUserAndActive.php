<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHospitalUserAndActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($user->role !== 'hospital') {
            return response()->json([
                'message' => 'Access denied. Account is ineligible.',
            ], 403);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'Access denied. Account is ineligible.',
            ], 403);
        }

        if (! $user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Access denied. Account is ineligible.',
            ], 403);
        }

        if (! $user->hospital_id || ! $user->hospital) {
            return response()->json([
                'message' => 'Access denied. Account is ineligible.',
            ], 403);
        }

        if ($user->hospital->status !== 'active') {
            return response()->json([
                'message' => 'Access denied. Account is ineligible.',
            ], 403);
        }

        return $next($request);
    }
}
