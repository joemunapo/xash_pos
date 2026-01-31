<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\HandleInertiaRequests;
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

        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'webhook',
            '/webhook',
            'broadcasting/auth',
        ]);
        $middleware->alias([
            'redirect.dashboard.by.role' => \App\Http\Middleware\RedirectDashboardByRole::class,
            'role' => CheckRole::class,
            'superadmin' => \App\Http\Middleware\SuperAdminMiddleware::class,
            'tenant.scope' => \App\Http\Middleware\TenantScopeMiddleware::class,
            'subscription.active' => \App\Http\Middleware\SubscriptionMiddleware::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
