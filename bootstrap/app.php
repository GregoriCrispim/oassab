<?php

use App\Http\Middleware\CachePublicPages;
use App\Http\Middleware\EnsurePatrimonioAccess;
use App\Http\Middleware\EnsureUserIsAdmin;
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
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'patrimonio' => EnsurePatrimonioAccess::class,
            'page.cache' => CachePublicPages::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('admin.login'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
