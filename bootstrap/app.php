<?php


use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Default web middleware (Inertia.js setup)
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        // Register your custom middleware aliases (NEW ADDITION)
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class, // Default
            'admin' => \App\Http\Middleware\AdminMiddleware::class, // Your custom
            'donor' => \App\Http\Middleware\DonorMiddleware::class, // Your custom
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Exception handling (leave as-is)
    })->create();