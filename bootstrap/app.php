<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('notify:low-stock')->dailyAt('08:00');
        $schedule->command('notify:expiring-products')->dailyAt('08:00');
    })
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // ¡Agregamos esta excepción!
        $middleware->validateCsrfTokens(except: [
            'webhook/*',
            '/webhook/conekta'
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
