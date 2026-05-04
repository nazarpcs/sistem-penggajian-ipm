<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\SanitizeInput;
use App\Http\Middleware\ThrottleLoginAttempts;
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
        // Middleware global: sanitasi semua input untuk mencegah XSS/CSV injection
        $middleware->append(SanitizeInput::class);

        // Alias middleware untuk penggunaan di route
        $middleware->alias([
            'role' => CheckRole::class,
            'throttle.login' => ThrottleLoginAttempts::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
