<?php

// Feature: employee-payroll-system, Task 3: Autentikasi & Keamanan
// Unit test untuk ThrottleLoginAttempts Middleware — rate limiting login
// @see Req 1.9 (rate limiting 10 percobaan/menit/IP)

use App\Http\Middleware\ThrottleLoginAttempts;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    RateLimiter::clear('login_attempt:127.0.0.1');
});

function createThrottleRequest(): Request
{
    $request = Request::create('/login', 'POST');
    $request->server->set('REMOTE_ADDR', '127.0.0.1');

    return $request;
}

function throttlePassThrough(): Closure
{
    return fn (Request $request) => new Response('OK', 200);
}

// ============================================================
// Izinkan request jika belum melebihi limit
// ============================================================

// Feature: employee-payroll-system, Property 2: Penolakan Kredensial Tidak Valid
it('should allow request when rate limit is not exceeded', function () {
    $request = createThrottleRequest();
    $middleware = new ThrottleLoginAttempts();

    $response = $middleware->handle($request, throttlePassThrough());

    expect($response->getStatusCode())->toBe(200);
});

// ============================================================
// Return 429 jika melebihi 10 attempts per menit
// ============================================================

// Feature: employee-payroll-system, Property 2: Penolakan Kredensial Tidak Valid
it('should return 429 when exceeding 10 attempts per minute', function () {
    $middleware = new ThrottleLoginAttempts();

    // Kirim 10 request yang berhasil (mengisi rate limiter)
    for ($i = 0; $i < 10; $i++) {
        $request = createThrottleRequest();
        $middleware->handle($request, throttlePassThrough());
    }

    // Request ke-11 harus ditolak
    $request = createThrottleRequest();
    $response = $middleware->handle($request, throttlePassThrough());

    expect($response->getStatusCode())->toBe(429);
});

// ============================================================
// Include Retry-After header saat throttled
// ============================================================

// Feature: employee-payroll-system, Property 2: Penolakan Kredensial Tidak Valid
it('should include Retry-After header when throttled', function () {
    $middleware = new ThrottleLoginAttempts();

    // Exhaust rate limit
    for ($i = 0; $i < 10; $i++) {
        $request = createThrottleRequest();
        $middleware->handle($request, throttlePassThrough());
    }

    // Request ke-11 — harus ada Retry-After header
    $request = createThrottleRequest();
    $response = $middleware->handle($request, throttlePassThrough());

    expect($response->getStatusCode())->toBe(429)
        ->and($response->headers->has('Retry-After'))->toBeTrue()
        ->and((int) $response->headers->get('Retry-After'))->toBeGreaterThan(0);
});

it('should include X-RateLimit-Remaining as 0 when throttled', function () {
    $middleware = new ThrottleLoginAttempts();

    for ($i = 0; $i < 10; $i++) {
        $request = createThrottleRequest();
        $middleware->handle($request, throttlePassThrough());
    }

    $request = createThrottleRequest();
    $response = $middleware->handle($request, throttlePassThrough());

    expect($response->getStatusCode())->toBe(429)
        ->and($response->headers->get('X-RateLimit-Remaining'))->toBe('0');
});
