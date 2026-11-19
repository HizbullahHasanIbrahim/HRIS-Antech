<?php

use App\Exceptions\BusinessRuleViolationException;
use App\Exceptions\ConcurrencyConflictException;
use App\Exceptions\UnauthorizedActionException;
use App\Http\Middleware\ClearAuthGuardsMiddleware;
use App\Http\Middleware\EnforceUnitScopeMiddleware;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Http\Middleware\TransformsRequest;
use Illuminate\Foundation\Http\Middleware\TrimStrings;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'unit.scope' => EnforceUnitScopeMiddleware::class,
            'auth' => Authenticate::class,
        ]);
        $middleware->prepend(ClearAuthGuardsMiddleware::class);
        $middleware->web(append: [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            AuthenticateSession::class,
            ShareErrorsFromSession::class,
            ValidatePostSize::class,
            TransformsRequest::class,
            ConvertEmptyStringsToNull::class,
            TrimStrings::class,
            PreventRequestForgery::class,
            HandleInertiaRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
        $exceptions->render(fn (ConcurrencyConflictException $e) => response()->json(['success' => false, 'error' => ['code' => 'CONCURRENCY_CONFLICT', 'message' => $e->getMessage()]], 409)
        );
        $exceptions->render(fn (UnauthorizedActionException $e) => response()->json(['success' => false, 'error' => ['code' => 'UNAUTHORIZED_ACTION', 'message' => $e->getMessage()]], 403)
        );
        $exceptions->render(fn (BusinessRuleViolationException $e) => response()->json(['success' => false, 'error' => ['code' => 'BUSINESS_RULE_VIOLATION', 'message' => $e->getMessage(), 'details' => $e->details ?? null]], 422)
        );
        $exceptions->render(function (Request $request, Throwable $e) {
            if ($request->header('X-Inertia')) {
                return back()->withErrors(['message' => $e->getMessage()]);
            }

            return response()->json(['success' => false, 'error' => ['code' => 'UNHANDLED_ERROR', 'message' => $e->getMessage()]], 500);
        });
    })->create();
