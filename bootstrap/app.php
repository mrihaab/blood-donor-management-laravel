<?php

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            RateLimiter::for('hospital-login', function (Request $request) {
                $email = (string) $request->input('email');
                return Limit::perMinute(5)->by(Str::lower(trim($email)).'|'.$request->ip());
            });

            RateLimiter::for('hospital-api', function (Request $request) {
                return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
            });
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Trust all reverse proxies (Render, Cloudflare, Load Balancers)
        $middleware->trustProxies(at: '*');

        // Default web middleware (Inertia.js, Security Headers & History Cache Prevention)
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\PreventBackHistoryCache::class,
        ]);

        // Register custom middleware aliases
        $middleware->alias([
            'auth'            => \App\Http\Middleware\Authenticate::class,
            'admin'           => \App\Http\Middleware\AdminMiddleware::class,
            'donor'           => \App\Http\Middleware\DonorMiddleware::class,
            'role'            => \App\Http\Middleware\RoleMiddleware::class,
            'active_status'   => \App\Http\Middleware\EnsureUserIsActive::class,
            '2fa'             => \App\Http\Middleware\EnforceTwoFactor::class,
            'hospital.active' => \App\Http\Middleware\EnsureHospitalUserAndActive::class,
            'abilities'       => \Laravel\Sanctum\Http\Middleware\CheckAbilities::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->report(function (Throwable $e) {
            Log::error('System Exception Captured', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        });
    })->create();