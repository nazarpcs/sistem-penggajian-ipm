<?php

// Feature: employee-payroll-system, Task 3: Autentikasi & Keamanan
// Feature test untuk alur login/logout end-to-end
// @see Property 1: Autentikasi Kredensial Valid
// @see Property 2: Penolakan Kredensial Tidak Valid
// @see Property 3: Logout Menghapus Sesi (Round-Trip)
// @see Property 4: Password Selalu Tersimpan Sebagai Hash Bcrypt
// @see Property 17: Invariant Audit Log
// @see Req 1.1-1.6

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Daftarkan route yang dibutuhkan untuk testing
    Route::middleware(['web'])->group(function () {
        Route::get('/login', [\App\Http\Controllers\Auth\AuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [\App\Http\Controllers\Auth\AuthController::class, 'login'])->name('login.post');
        Route::post('/logout', [\App\Http\Controllers\Auth\AuthController::class, 'logout'])->name('logout');

        Route::middleware(['auth'])->group(function () {
            Route::get('/admin/dashboard', fn () => response('Admin Dashboard'))->name('admin.dashboard');
            Route::get('/owner/dashboard', fn () => response('Owner Dashboard'))->name('owner.dashboard');
            Route::get('/karyawan/dashboard', fn () => response('Karyawan Dashboard'))->name('karyawan.dashboard');
        });
    });
});

// ============================================================
// Login berhasil → redirect ke dashboard sesuai peran
// ============================================================

// Feature: employee-payroll-system, Property 1: Autentikasi Kredensial Valid
describe('login success redirect', function () {
    it('should redirect to admin dashboard on successful admin login', function () {
        User::factory()->admin()->create([
            'email' => 'admin@ipm.test',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@ipm.test',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();
    });

    it('should redirect to owner dashboard on successful pemilik_pt login', function () {
        User::factory()->pemilikPt()->create([
            'email' => 'owner@ipm.test',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'owner@ipm.test',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('owner.dashboard'));
        $this->assertAuthenticated();
    });

    it('should redirect to karyawan dashboard on successful karyawan login', function () {
        User::factory()->karyawan()->create([
            'email' => 'karyawan@ipm.test',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'karyawan@ipm.test',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('karyawan.dashboard'));
        $this->assertAuthenticated();
    });
});

// ============================================================
// Login gagal → redirect back dengan error
// ============================================================

// Feature: employee-payroll-system, Property 2: Penolakan Kredensial Tidak Valid
describe('login fail error', function () {
    it('should redirect back with error when email is wrong', function () {
        User::factory()->create([
            'email' => 'real@ipm.test',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'wrong@ipm.test',
            'password' => 'password123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    });

    it('should redirect back with error when password is wrong', function () {
        $user = User::factory()->create([
            'email' => 'user@ipm.test',
            'password' => Hash::make('correctpass'),
        ]);

        $response = $this->post('/login', [
            'email' => 'user@ipm.test',
            'password' => 'wrongpass',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
        $this->assertGuest();

        $user->refresh();
        expect($user->login_attempts)->toBe(1);
    });

    it('should reject login for inactive account', function () {
        User::factory()->inactive()->create([
            'email' => 'inactive@ipm.test',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'inactive@ipm.test',
            'password' => 'password123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    });

    it('should show locked message when trying to login with locked account', function () {
        User::factory()->locked()->create([
            'email' => 'locked@ipm.test',
            'password' => Hash::make('correctpass'),
        ]);

        $response = $this->post('/login', [
            'email' => 'locked@ipm.test',
            'password' => 'correctpass',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    });
});

// ============================================================
// Lockout setelah 5x gagal
// ============================================================

// Feature: employee-payroll-system, Property 2: Penolakan Kredensial Tidak Valid
describe('lockout after 5 fails', function () {
    it('should lock account after 5 consecutive failed login attempts', function () {
        $user = User::factory()->create([
            'email' => 'lockme@ipm.test',
            'password' => Hash::make('correctpass'),
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'lockme@ipm.test',
                'password' => 'wrongpass',
            ]);
        }

        $user->refresh();
        expect($user->login_attempts)->toBe(5)
            ->and($user->locked_until)->not->toBeNull()
            ->and($user->locked_until->isFuture())->toBeTrue();
    });

    it('should reject login with correct password after lockout', function () {
        $user = User::factory()->create([
            'email' => 'locktest@ipm.test',
            'password' => Hash::make('correctpass'),
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'locktest@ipm.test',
                'password' => 'wrongpass',
            ]);
        }

        $response = $this->post('/login', [
            'email' => 'locktest@ipm.test',
            'password' => 'correctpass',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    });
});

// ============================================================
// Logout → session dihapus
// ============================================================

// Feature: employee-payroll-system, Property 3: Logout Menghapus Sesi (Round-Trip)
describe('logout clears session', function () {
    it('should destroy session and redirect to login on logout', function () {
        $user = User::factory()->admin()->create();

        $this->actingAs($user);
        $this->assertAuthenticated();

        $response = $this->post('/logout');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    });

    it('should not be able to access protected route after logout', function () {
        $user = User::factory()->admin()->create();

        $this->actingAs($user);
        $this->assertAuthenticated();

        $this->post('/logout');
        $this->assertGuest();

        $response = $this->get('/admin/dashboard');
        $response->assertRedirect(route('login'));
    });
});

// ============================================================
// Audit log dicatat
// ============================================================

// Feature: employee-payroll-system, Property 17: Invariant Audit Log
describe('audit log recorded', function () {
    it('should create audit log entry on successful login', function () {
        User::factory()->admin()->create([
            'email' => 'audit@ipm.test',
            'password' => Hash::make('password123'),
        ]);

        $this->post('/login', [
            'email' => 'audit@ipm.test',
            'password' => 'password123',
        ]);

        $auditLog = AuditLog::where('jenis_aktivitas', 'login')
            ->where('data_baru->email', 'audit@ipm.test')
            ->first();

        expect($auditLog)->not->toBeNull()
            ->and($auditLog->jenis_aktivitas)->toBe('login')
            ->and($auditLog->model_tipe)->toBe('User');
    });

    it('should create audit log entry on failed login', function () {
        $user = User::factory()->create([
            'email' => 'auditfail@ipm.test',
            'password' => Hash::make('password123'),
        ]);

        $this->post('/login', [
            'email' => 'auditfail@ipm.test',
            'password' => 'wrongpassword',
        ]);

        $auditLog = AuditLog::where('jenis_aktivitas', 'login_gagal')
            ->where('model_id', $user->id)
            ->first();

        expect($auditLog)->not->toBeNull()
            ->and($auditLog->jenis_aktivitas)->toBe('login_gagal')
            ->and($auditLog->model_tipe)->toBe('User');
    });

    it('should create audit log entry on logout', function () {
        $user = User::factory()->admin()->create([
            'email' => 'auditlogout@ipm.test',
        ]);

        $this->actingAs($user);
        $this->post('/logout');

        $auditLog = AuditLog::where('jenis_aktivitas', 'logout')
            ->where('user_id', $user->id)
            ->first();

        expect($auditLog)->not->toBeNull()
            ->and($auditLog->jenis_aktivitas)->toBe('logout')
            ->and($auditLog->model_tipe)->toBe('User')
            ->and($auditLog->model_id)->toBe($user->id);
    });
});

// ============================================================
// Password tersimpan sebagai hash bcrypt
// ============================================================

// Feature: employee-payroll-system, Property 4: Password Selalu Tersimpan Sebagai Hash Bcrypt
describe('password stored as bcrypt hash', function () {
    it('should store password as bcrypt hash, never plaintext', function () {
        $plainPassword = 'MySecurePassword123!';

        $user = User::factory()->create([
            'password' => Hash::make($plainPassword),
        ]);

        $rawPassword = $user->getRawOriginal('password');
        expect($rawPassword)->not->toBe($plainPassword)
            ->and(Hash::check($plainPassword, $rawPassword))->toBeTrue()
            ->and(str_starts_with($rawPassword, '$2y$'))->toBeTrue();
    });

    it('should verify password via Hash::check after creation', function () {
        $plainPassword = 'AnotherSecure456!';

        $user = User::factory()->create([
            'password' => Hash::make($plainPassword),
        ]);

        $rawPassword = $user->getRawOriginal('password');

        expect(Hash::check($plainPassword, $rawPassword))->toBeTrue();
        expect(Hash::check('wrongpassword', $rawPassword))->toBeFalse();
    });
});
