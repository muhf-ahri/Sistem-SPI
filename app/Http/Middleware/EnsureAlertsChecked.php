<?php

namespace App\Http\Middleware;

use App\Services\AlertNotificationService;
use Closure;
use Illuminate\Http\Request;

class EnsureAlertsChecked
{
    protected static bool $handled = false;

    public function handle(Request $request, Closure $next)
    {
        if (!static::$handled && auth()->check()) {
            try {
                (new AlertNotificationService)->checkFor(auth()->user());
            } catch (\Throwable $e) {
                // Jangan menggagalkan request bila pemeriksaan peringatan gagal
            }
            static::$handled = true;
        }

        return $next($request);
    }
}
