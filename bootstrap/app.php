<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);
        $middleware->validateCsrfTokens(except: [
        // soalnya kalo ga diginiin pas masi localhost nanti error pokoknya gitulah
        'logout',
        'leave/requests/*/approve',
        'leave/requests/*/reject',
        'leave/requests/approve-all',
        'portal/leave-request',
        'portal/leave/*/withdraw',
        'portal/clock-in',
        'portal/clock-out'
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
