<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckRole; // import middleware

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);

        // Pemicuan notifikasi peringatan otomatis (on-request) — cukup php artisan serve
        $middleware->web(append: [\App\Http\Middleware\EnsureAlertsChecked::class]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();