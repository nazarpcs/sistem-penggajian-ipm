<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware sanitasi input — mencegah XSS dan CSV Injection.
 *
 * - Strip HTML tags dari semua input string
 * - Escape karakter berbahaya di awal string (=, +, -, @) untuk CSV injection
 * - Skip field 'password' dan 'password_confirmation' dari sanitasi
 *
 * @see Req 12.2 (sanitasi input untuk mencegah XSS dan SQL Injection)
 */
class SanitizeInput
{
    /**
     * Field yang dikecualikan dari sanitasi.
     * Password tidak boleh diubah karena akan merusak hash verification.
     *
     * @var list<string>
     */
    private const EXCLUDED_FIELDS = [
        'password',
        'password_confirmation',
        'current_password',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();

        $sanitized = $this->sanitizeArray($input);

        $request->merge($sanitized);

        return $next($request);
    }

    /**
     * Sanitasi array input secara rekursif.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function sanitizeArray(array $data, string $parentKey = ''): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            $fullKey = $parentKey !== '' ? "{$parentKey}.{$key}" : (string) $key;

            if (in_array((string) $key, self::EXCLUDED_FIELDS, true)) {
                $result[$key] = $value;
                continue;
            }

            if (is_array($value)) {
                $result[$key] = $this->sanitizeArray($value, $fullKey);
            } elseif (is_string($value)) {
                $result[$key] = $this->sanitizeString($value);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Sanitasi satu string value.
     *
     * 1. Strip HTML tags untuk mencegah XSS
     * 2. Escape karakter CSV injection di awal string
     */
    private function sanitizeString(string $value): string
    {
        // Strip HTML tags untuk mencegah XSS
        $value = strip_tags($value);

        // Escape karakter CSV injection di awal string (=, +, -, @)
        if ($value !== '' && in_array($value[0], ['=', '+', '-', '@'], true)) {
            $value = "'" . $value;
        }

        return $value;
    }
}
