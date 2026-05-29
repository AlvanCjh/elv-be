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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'project.scope' => \App\Http\Middleware\ProjectScopeMiddleware::class,
        ]);
        $middleware->appendToGroup('api', \App\Http\Middleware\UpdateUserLastSeen::class);
        $middleware->appendToGroup('api', \App\Http\Middleware\CheckBlocked::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->reportable(function (\Illuminate\Validation\ValidationException $e) {
            \Illuminate\Support\Facades\Log::error('Validation Failed:', $e->errors());
        });
    })->create();