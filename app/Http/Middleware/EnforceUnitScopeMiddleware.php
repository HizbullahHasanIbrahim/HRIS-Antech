<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceUnitScopeMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // Global BSDM role gets bypass
        if ($user->hasRole('Admin BSDM') || $user->hasRole('Super Admin')) {
            return $next($request);
        }

        // Restrict queries using unit_id from user's primary department/assignment
        $unitId = $user->employee?->unit_id;

        if ($unitId) {
            // Merge unit_id to enforce scope on requests that read lists
            if ($request->isMethod('GET')) {
                $request->merge(['unit_id' => $unitId]);
            }
        }

        return $next($request);
    }
}
