<?php

use App\Enums\UserRole;
use App\Http\Middleware\EnsureDealerApproved;
use App\Http\Middleware\EnsureTwoFactorAuthenticated;
use App\Http\Middleware\IsClientUser;
use App\Http\Middleware\IsOwnerUser;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Sentry\Laravel\Integration;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'owner' => IsOwnerUser::class,
            'client' => IsClientUser::class,
            'dealer_approved' => EnsureDealerApproved::class,
            'two_factor' => EnsureTwoFactorAuthenticated::class,
        ]);

        // Already-authenticated users hitting guest routes (e.g. "/" -> /login)
        // must be routed by role; the framework default sends everyone to
        // /dashboard, which 403s dealer accounts.
        $middleware->redirectUsersTo(function (Request $request): string {
            return in_array($request->user()?->role, [UserRole::DealerOwner, UserRole::DealerUser], true)
                ? '/my/dashboard'
                : '/dashboard';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        Integration::handles($exceptions);
    })->create();
