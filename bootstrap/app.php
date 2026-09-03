<?php

use App\Http\Middleware\ConvertAuthTokenCookieToBearerHeader;
use App\Http\Middleware\EnsureAdminUser;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Modules\Identity\Http\Middleware\CheckPermission;
use Modules\Identity\Http\Middleware\CheckRole;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureAdminUser::class,
            'role' => CheckRole::class,
            'permission' => CheckPermission::class,
            'convert.auth.cookie' => ConvertAuthTokenCookieToBearerHeader::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
