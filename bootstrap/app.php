<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // API middleware group
        $middleware->group('api', [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // CSRF token validation exceptions (for API routes that need it)
        $middleware->validateCsrfTokens(except: [
            'sanctum/csrf-cookie', // Allow CSRF cookie endpoint
            'api/auth/login',      // Your login endpoint
            'api/auth/register',   // Your register endpoint
        ]);

        // Sanctum configuration for SPA authentication
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Custom exception handling can go here
    })
    ->create();
