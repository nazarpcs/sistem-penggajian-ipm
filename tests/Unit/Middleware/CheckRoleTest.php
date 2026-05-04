<?php

// Feature: employee-payroll-system, Task 3: Autentikasi & Keamanan
// Unit test untuk CheckRole Middleware — validasi RBAC pada level route
// @see Property 5: RBAC — Akses Sesuai Peran
// @see Req 2.5, 2.6

use App\Http\Middleware\CheckRole;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

uses(RefreshDatabase::class);

function createCheckRoleRequest(?User $user = null): Request
{
    $request = Request::create('/test-route', 'GET');

    if ($user !== null) {
        $request->setUserResolver(fn () => $user);
    }

    return $request;
}

function passThrough(): Closure
{
    return fn (Request $request) => new Response('OK', 200);
}

// ============================================================
// Izinkan akses jika role user cocok (single role)
// ============================================================

// Feature: employee-payroll-system, Property 5: RBAC — Akses Sesuai Peran
describe('allow matching role', function () {
    it('should allow access when user role matches the required role', function () {
        $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $request = createCheckRoleRequest($user);
        $middleware = new CheckRole();

        $response = $middleware->handle($request, passThrough(), 'admin');

        expect($response->getStatusCode())->toBe(200);
    });

    it('should allow pemilik_pt to access pemilik_pt route', function () {
        $user = User::factory()->create(['role' => 'pemilik_pt', 'is_active' => true]);
        $request = createCheckRoleRequest($user);
        $middleware = new CheckRole();

        $response = $middleware->handle($request, passThrough(), 'pemilik_pt');

        expect($response->getStatusCode())->toBe(200);
    });

    it('should allow karyawan to access karyawan route', function () {
        $user = User::factory()->create(['role' => 'karyawan', 'is_active' => true]);
        $request = createCheckRoleRequest($user);
        $middleware = new CheckRole();

        $response = $middleware->handle($request, passThrough(), 'karyawan');

        expect($response->getStatusCode())->toBe(200);
    });
});

// ============================================================
// Izinkan akses jika role user cocok (multiple roles comma-separated)
// ============================================================

// Feature: employee-payroll-system, Property 5: RBAC — Akses Sesuai Peran
describe('allow multiple roles', function () {
    it('should allow access when user role matches one of multiple comma-separated roles', function () {
        $user = User::factory()->create(['role' => 'pemilik_pt', 'is_active' => true]);
        $request = createCheckRoleRequest($user);
        $middleware = new CheckRole();

        $response = $middleware->handle($request, passThrough(), 'admin,pemilik_pt');

        expect($response->getStatusCode())->toBe(200);
    });

    it('should allow admin when route accepts admin,pemilik_pt,karyawan', function () {
        $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $request = createCheckRoleRequest($user);
        $middleware = new CheckRole();

        $response = $middleware->handle($request, passThrough(), 'admin,pemilik_pt,karyawan');

        expect($response->getStatusCode())->toBe(200);
    });

    it('should handle roles with extra whitespace in comma-separated list', function () {
        $user = User::factory()->create(['role' => 'karyawan', 'is_active' => true]);
        $request = createCheckRoleRequest($user);
        $middleware = new CheckRole();

        $response = $middleware->handle($request, passThrough(), 'admin , karyawan');

        expect($response->getStatusCode())->toBe(200);
    });
});

// ============================================================
// Tolak akses (403) jika role user tidak cocok
// ============================================================

// Feature: employee-payroll-system, Property 5: RBAC — Akses Sesuai Peran
describe('deny non-matching role with 403', function () {
    it('should abort 403 when karyawan accesses admin-only route', function () {
        $user = User::factory()->create(['role' => 'karyawan', 'is_active' => true]);
        $request = createCheckRoleRequest($user);
        $middleware = new CheckRole();

        $middleware->handle($request, passThrough(), 'admin');
    })->throws(\Symfony\Component\HttpKernel\Exception\HttpException::class);

    it('should abort 403 when pemilik_pt accesses admin-only route', function () {
        $user = User::factory()->create(['role' => 'pemilik_pt', 'is_active' => true]);
        $request = createCheckRoleRequest($user);
        $middleware = new CheckRole();

        $middleware->handle($request, passThrough(), 'admin');
    })->throws(\Symfony\Component\HttpKernel\Exception\HttpException::class);

    it('should abort 403 when karyawan accesses admin,pemilik_pt route', function () {
        $user = User::factory()->create(['role' => 'karyawan', 'is_active' => true]);
        $request = createCheckRoleRequest($user);
        $middleware = new CheckRole();

        $middleware->handle($request, passThrough(), 'admin,pemilik_pt');
    })->throws(\Symfony\Component\HttpKernel\Exception\HttpException::class);

    it('should create audit log entry on unauthorized access', function () {
        $user = User::factory()->create(['role' => 'karyawan', 'is_active' => true]);
        $this->actingAs($user);
        $request = createCheckRoleRequest($user);
        $middleware = new CheckRole();

        try {
            $middleware->handle($request, passThrough(), 'admin');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            // Expected 403
        }

        $auditLog = AuditLog::where('jenis_aktivitas', 'akses_tidak_sah')
            ->where('user_id', $user->id)
            ->first();

        expect($auditLog)->not->toBeNull()
            ->and($auditLog->data_baru['role_user'])->toBe('karyawan')
            ->and($auditLog->data_baru['role_dibutuhkan'])->toContain('admin');
    });
});

// ============================================================
// Redirect ke login jika user null (belum login)
// ============================================================

// Feature: employee-payroll-system, Property 5: RBAC — Akses Sesuai Peran
describe('redirect if no user', function () {
    it('should redirect to login when user is null (not authenticated)', function () {
        $request = Request::create('/admin/dashboard', 'GET');
        // User resolver returns null (no authenticated user)
        $request->setUserResolver(fn () => null);
        $middleware = new CheckRole();

        $response = $middleware->handle($request, passThrough(), 'admin');

        expect($response->getStatusCode())->toBe(302)
            ->and($response->headers->get('Location'))->toContain('login');
    });
});
