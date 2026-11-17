<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ClearAuthGuardsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // ponytail: token guard caches user across requests in the test harness;
        // forgetGuards() must run BEFORE auth middleware to prevent stale user resolution.
        Auth::forgetGuards();

        return $next($request);
    }
}
