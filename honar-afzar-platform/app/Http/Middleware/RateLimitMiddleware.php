<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $key = 'global', int $maxAttempts = 60, int $decayMinutes = 1): Response
    {
        $rateLimitKey = $this->resolveRateLimitKey($request, $key);

        if (RateLimiter::tooManyAttempts($rateLimitKey, $maxAttempts)) {
            $retryAfter = RateLimiter::availableIn($rateLimitKey);
            
            return response()->json([
                'success' => false,
                'message' => 'تعداد درخواست‌ها بیش از حد مجاز است. لطفاً ' . $retryAfter . ' ثانیه صبر کنید.',
                'retry_after' => $retryAfter,
            ], 429);
        }

        RateLimiter::hit($rateLimitKey, $decayMinutes * 60);

        $response = $next($request);

        return $this->addHeaders($response, $maxAttempts, RateLimiter::remaining($rateLimitKey, $maxAttempts));
    }

    /**
     * Resolve rate limit key
     */
    protected function resolveRateLimitKey(Request $request, string $key): string
    {
        return $key . '|' . ($request->user()?->id ?? $request->ip());
    }

    /**
     * Add rate limit headers to response
     */
    protected function addHeaders(Response $response, int $maxAttempts, int $remainingAttempts): Response
    {
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', $remainingAttempts);

        return $response;
    }
}
