<?php

// Feature: employee-payroll-system, Task 3: Autentikasi & Keamanan
// Unit test untuk SanitizeInput Middleware — pencegahan XSS dan CSV Injection
// @see Req 12.2 (sanitasi input untuk mencegah XSS dan SQL Injection)

use App\Http\Middleware\SanitizeInput;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

function createSanitizeRequest(array $data): Request
{
    return Request::create('/test', 'POST', $data);
}

function sanitizePassThrough(): Closure
{
    return fn (Request $request) => new Response('OK', 200);
}

// ============================================================
// Strip HTML tags dari input string
// ============================================================

describe('strip HTML tags', function () {
    it('should strip script tags from string input', function () {
        $request = createSanitizeRequest([
            'name' => '<script>alert("xss")</script>John',
        ]);

        $middleware = new SanitizeInput();
        $middleware->handle($request, sanitizePassThrough());

        expect($request->input('name'))->toBe('alert("xss")John');
    });

    it('should strip nested HTML tags', function () {
        $request = createSanitizeRequest([
            'comment' => '<div><b>Bold</b> text</div>',
        ]);

        $middleware = new SanitizeInput();
        $middleware->handle($request, sanitizePassThrough());

        expect($request->input('comment'))->toBe('Bold text');
    });

    it('should strip img tags with onerror attribute', function () {
        $request = createSanitizeRequest([
            'field' => '<img src=x onerror=alert(1)>test',
        ]);

        $middleware = new SanitizeInput();
        $middleware->handle($request, sanitizePassThrough());

        expect($request->input('field'))->toBe('test');
    });

    it('should strip iframe tags', function () {
        $request = createSanitizeRequest([
            'field' => '<iframe src="evil.com"></iframe>content',
        ]);

        $middleware = new SanitizeInput();
        $middleware->handle($request, sanitizePassThrough());

        expect($request->input('field'))->toBe('content');
    });

    it('should not modify plain text without HTML', function () {
        $request = createSanitizeRequest([
            'name' => 'John Doe',
        ]);

        $middleware = new SanitizeInput();
        $middleware->handle($request, sanitizePassThrough());

        expect($request->input('name'))->toBe('John Doe');
    });
});

// ============================================================
// Escape CSV injection chars (=, +, -, @) di awal string
// ============================================================

describe('escape CSV injection', function () {
    it('should escape equals sign at the beginning of string', function () {
        $request = createSanitizeRequest(['field' => '=cmd("malicious")']);

        $middleware = new SanitizeInput();
        $middleware->handle($request, sanitizePassThrough());

        expect($request->input('field'))->toBe("'=cmd(\"malicious\")");
    });

    it('should escape plus sign at the beginning of string', function () {
        $request = createSanitizeRequest(['field' => '+cmd("malicious")']);

        $middleware = new SanitizeInput();
        $middleware->handle($request, sanitizePassThrough());

        expect($request->input('field'))->toBe("'+cmd(\"malicious\")");
    });

    it('should escape minus sign at the beginning of string', function () {
        $request = createSanitizeRequest(['field' => '-cmd("malicious")']);

        $middleware = new SanitizeInput();
        $middleware->handle($request, sanitizePassThrough());

        expect($request->input('field'))->toBe("'-cmd(\"malicious\")");
    });

    it('should escape at sign at the beginning of string', function () {
        $request = createSanitizeRequest(['field' => '@SUM(A1:A10)']);

        $middleware = new SanitizeInput();
        $middleware->handle($request, sanitizePassThrough());

        expect($request->input('field'))->toBe("'@SUM(A1:A10)");
    });

    it('should not escape CSV chars in the middle of string', function () {
        $request = createSanitizeRequest([
            'field' => 'normal+text@here',
        ]);

        $middleware = new SanitizeInput();
        $middleware->handle($request, sanitizePassThrough());

        expect($request->input('field'))->toBe('normal+text@here');
    });
});

// ============================================================
// Skip sanitasi untuk field password dan password_confirmation
// ============================================================

describe('skip password fields', function () {
    it('should not sanitize password field', function () {
        $rawPassword = '<script>P@ss=word+123</script>';
        $request = createSanitizeRequest(['password' => $rawPassword]);

        $middleware = new SanitizeInput();
        $middleware->handle($request, sanitizePassThrough());

        expect($request->input('password'))->toBe($rawPassword);
    });

    it('should not sanitize password_confirmation field', function () {
        $rawPassword = '<b>+SecurePass!</b>';
        $request = createSanitizeRequest(['password_confirmation' => $rawPassword]);

        $middleware = new SanitizeInput();
        $middleware->handle($request, sanitizePassThrough());

        expect($request->input('password_confirmation'))->toBe($rawPassword);
    });

    it('should not sanitize current_password field', function () {
        $rawPassword = '=OldP@ss<br>123';
        $request = createSanitizeRequest(['current_password' => $rawPassword]);

        $middleware = new SanitizeInput();
        $middleware->handle($request, sanitizePassThrough());

        expect($request->input('current_password'))->toBe($rawPassword);
    });

    it('should sanitize non-password fields while skipping password in same request', function () {
        $request = createSanitizeRequest([
            'name' => '<b>Admin</b>',
            'password' => '<script>P@ss</script>',
        ]);

        $middleware = new SanitizeInput();
        $middleware->handle($request, sanitizePassThrough());

        expect($request->input('name'))->toBe('Admin')
            ->and($request->input('password'))->toBe('<script>P@ss</script>');
    });
});

// ============================================================
// Handle nested array input
// ============================================================

describe('handle nested arrays', function () {
    it('should sanitize nested array input recursively', function () {
        $request = createSanitizeRequest([
            'data' => [
                'name' => '<em>Nested</em> value',
                'deep' => [
                    'field' => '<img src=x onerror=alert(1)>test',
                ],
            ],
        ]);

        $middleware = new SanitizeInput();
        $middleware->handle($request, sanitizePassThrough());

        expect($request->input('data.name'))->toBe('Nested value')
            ->and($request->input('data.deep.field'))->toBe('test');
    });

    it('should skip password fields inside nested arrays', function () {
        $request = createSanitizeRequest([
            'user' => [
                'name' => '<b>Test</b>',
                'password' => '<script>secret</script>',
            ],
        ]);

        $middleware = new SanitizeInput();
        $middleware->handle($request, sanitizePassThrough());

        expect($request->input('user.name'))->toBe('Test')
            ->and($request->input('user.password'))->toBe('<script>secret</script>');
    });

    it('should handle deeply nested CSV injection in arrays', function () {
        $request = createSanitizeRequest([
            'level1' => [
                'level2' => [
                    'field' => '=HYPERLINK("evil.com")',
                ],
            ],
        ]);

        $middleware = new SanitizeInput();
        $middleware->handle($request, sanitizePassThrough());

        expect($request->input('level1.level2.field'))->toStartWith("'");
    });
});

// ============================================================
// Tidak mengubah non-string values (integer, boolean, null)
// ============================================================

describe('preserve non-string values', function () {
    it('should not modify integers', function () {
        $request = createSanitizeRequest(['age' => 25]);

        $middleware = new SanitizeInput();
        $middleware->handle($request, sanitizePassThrough());

        expect($request->input('age'))->toBe(25);
    });

    it('should not modify booleans', function () {
        $request = createSanitizeRequest(['active' => true]);

        $middleware = new SanitizeInput();
        $middleware->handle($request, sanitizePassThrough());

        expect($request->input('active'))->toBe(true);
    });

    it('should not modify floats', function () {
        $request = createSanitizeRequest(['score' => 99.5]);

        $middleware = new SanitizeInput();
        $middleware->handle($request, sanitizePassThrough());

        expect($request->input('score'))->toBe(99.5);
    });

    it('should handle null values gracefully', function () {
        $request = createSanitizeRequest(['optional' => null]);

        $middleware = new SanitizeInput();
        $middleware->handle($request, sanitizePassThrough());

        expect($request->input('optional'))->toBeNull();
    });

    it('should handle empty string without error', function () {
        $request = createSanitizeRequest(['field' => '']);

        $middleware = new SanitizeInput();
        $middleware->handle($request, sanitizePassThrough());

        expect($request->input('field'))->toBe('');
    });

    it('should handle mixed types in same request', function () {
        $request = createSanitizeRequest([
            'name' => '<b>Test</b>',
            'age' => 30,
            'active' => true,
            'score' => 85.5,
            'notes' => null,
        ]);

        $middleware = new SanitizeInput();
        $middleware->handle($request, sanitizePassThrough());

        expect($request->input('name'))->toBe('Test')
            ->and($request->input('age'))->toBe(30)
            ->and($request->input('active'))->toBe(true)
            ->and($request->input('score'))->toBe(85.5)
            ->and($request->input('notes'))->toBeNull();
    });
});
