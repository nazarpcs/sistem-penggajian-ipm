<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware rate limiting — max 10 percobaan login per menit per IP.
 *
 * Menggunakan Laravel RateLimiter untuk membatasi percobaan login
 * dari satu alamat IP yang sama. Return 429 jika melebihi limit.
 *
 * @see Req 1.9 (rate limiting 10 percobaan/menit/IP)
 */
class ThrottleLoginAttempts
{
    /**
     * Batas maksimal percobaan per menit per IP.
     */
    private const MAX_ATTEMPTS = 10;

    /**
     * Durasi decay dalam detik (1 menit).
     */
    private const DECAY_SECONDS = 60;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'login_attempt:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $retryAfter = RateLimiter::availableIn($key);

            return response()->json([
                'message' => "Terlalu banyak percobaan login. Coba lagi dalam {$retryAfter} detik.",
            ], 429)->withHeaders([
                'Retry-After' => $retryAfter,
                'X-RateLimit-Limit' => self::MAX_ATTEMPTS,
                'X-RateLimit-Remaining' => 0,
            ]);
        }

        RateLimiter::hit($key, self::DECAY_SECONDS);

        /** @var Response $response */
        $response = $next($request);

        $remaining = RateLimiter::remaining($key, self::MAX_ATTEMPTS);

        // Tambahkan rate limit headers ke response
        if (method_exists($response, 'withHeaders')) {
            $response->withHeaders([
                'X-RateLimit-Limit' => self::MAX_ATTEMPTS,
                'X-RateLimit-Remaining' => max(0, $remaining),
            ]);
        }

        return $response;
    }
}
