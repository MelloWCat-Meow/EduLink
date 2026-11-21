<?php

use App\Http\Middleware\EnsurePeran;
use App\Http\Middleware\EnsureStatusTutor;
use App\Http\Middleware\LepasPenahananKedaluwarsa;
use App\Http\Middleware\PastikanAkunAktif;
use App\Http\Middleware\PastikanModeDemo;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\AuthenticateSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'peran' => EnsurePeran::class,
            'status-tutor' => EnsureStatusTutor::class,
            'mode-demo' => PastikanModeDemo::class,
            'lepas-penahanan' => LepasPenahananKedaluwarsa::class,
        ]);

        // The payment gateway cannot send a CSRF token; webhooks are checked by signature.
        $middleware->validateCsrfTokens(except: ['webhooks/*']);

        // Logs out any session that still holds an old password hash, so a
        // password change or reset ends the account's other sessions.
        $middleware->web(append: [AuthenticateSession::class, PastikanAkunAktif::class]);

        $middleware->redirectGuestsTo(fn (): string => route('login'));
        $middleware->redirectUsersTo(fn (Request $request): string => route($request->user()->peran->homeRoute()));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
