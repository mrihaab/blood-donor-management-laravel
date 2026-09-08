<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request with rate-limiting and role-based redirection.
     */
    public function store(LoginRequest $request): \Symfony\Component\HttpFoundation\Response
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        Log::info('Successful user authentication', [
            'user_id' => $user->id,
            'role' => $user->role,
            'ip' => $request->ip(),
        ]);

        // Role-based redirect (Inertia::location triggers full browser page load to Blade views)
        if ($user->role === 'admin') {
            return Inertia::location(route('admin.dashboard'));
        } elseif ($user->role === 'hospital') {
            return Inertia::location(route('hospital.dashboard'));
        } elseif ($user->role === 'donor') {
            return Inertia::location(route('donor.dashboard'));
        }

        // Default fallback
        return Inertia::location(route('dashboard'));
    }

    /**
     * Destroy an authenticated session securely.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $userId = Auth::id();

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Log::info('User logged out successfully', ['user_id' => $userId]);

        return redirect()->route('login');
    }
}
