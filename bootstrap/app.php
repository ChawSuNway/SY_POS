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
    ->withMiddleware(function (Middleware $middleware): void {
        // Render/cloud proxy နောက်ကွယ်တွင် HTTPS URL + secure cookie မှန်စေရန်
        $middleware->trustProxies(at: '*');

        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\LogActivity::class,   // write request မှတ်တမ်း (terminable)
        ]);
        $middleware->alias([
            'role'        => \App\Http\Middleware\EnsureUserHasRole::class,
            'shop'        => \App\Http\Middleware\RequireShop::class,
            'deny_super'  => \App\Http\Middleware\DenySuperAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
