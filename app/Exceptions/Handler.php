<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class Handler extends ExceptionHandler
{
    /**
     * Render an exception into an HTTP response.
     */
    protected function renderException(Request $request, \Throwable $e): Response|\Symfony\Component\HttpFoundation\JsonResponse
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->renderApiException($request, $e);
        }

        return parent::renderException($request, $e);
    }

    /**
     * Render an exception into an HTTP JSON response.
     */
    protected function renderApiException(Request $request, \Throwable $e): JsonResponse
    {
        $statusCode = $this->getStatusCode($e);
        $message = $this->getErrorMessage($e);

        // Log the exception (including ACCESS_DENIED as per SDD Section 5)
        if ($e instanceof UnauthorizedActionException) {
            Log::warning('ACCESS_DENIED', [
                'user_id' => $request->user()?->id,
                'route' => $request->route()?->getName(),
                'action' => $request->method(),
                'path' => $request->path(),
                'message' => $e->getMessage(),
            ]);
        }

        return new JsonResponse([
            'success' => false,
            'error' => [
                'code' => $this->getErrorCode($e),
                'message' => $message,
                'details' => $e instanceof BusinessRuleViolationException ? $e->details : null,
            ],
            'timestamp' => now()->toIso8601String(),
        ], $statusCode);
    }

    /**
     * Map exception class to HTTP status code.
     */
    protected function getStatusCode(\Throwable $e): int
    {
        return match (true) {
            $e instanceof ConcurrencyConflictException => 409,
            $e instanceof UnauthorizedActionException => 403,
            $e instanceof BusinessRuleViolationException => 422,
            default => 500,
        };
    }

    /**
     * Get error code from exception.
     */
    protected function getErrorCode(\Throwable $e): string
    {
        return match (true) {
            $e instanceof ConcurrencyConflictException => 'CONFLICT',
            $e instanceof UnauthorizedActionException => 'UNAUTHORIZED',
            $e instanceof BusinessRuleViolationException => 'VALIDATION_ERROR',
            default => 'INTERNAL_ERROR',
        };
    }

    /**
     * Get error message from exception.
     */
    protected function getErrorMessage(\Throwable $e): string
    {
        return match (true) {
            $e instanceof ConcurrencyConflictException => 'Resource has been modified by another user. Please refresh and try again.',
            $e instanceof UnauthorizedActionException => 'You are not authorized to perform this action.',
            $e instanceof BusinessRuleViolationException => $e->getMessage(),
            default => 'An internal error occurred. Please try again later.',
        };
    }
}
