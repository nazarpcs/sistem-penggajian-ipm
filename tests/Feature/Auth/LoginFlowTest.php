<?php

// Feature: employee-payroll-system, Task 3: Autentikasi & Keamanan
// Feature test untuk alur login/logout end-to-end (LoginFlowTest)
// @see Property 1: Autentikasi Kredensial Valid
// @see Property 2: Penolakan Kredensial Tidak Valid
// @see Property 3: Logout Menghapus Sesi (Round-Trip)
// @see Property 4: Password Selalu Tersimpan Sebagai Hash Bcrypt
// @see Property 5: RBAC — Akses Sesuai Peran (redirect per role)
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
    // (Route lengkap akan dikonfigurasi di Task 17.1)
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
// Login berhasil → redirect ke dashboard sesuai role
// ============================================================

// Feature: employee-payroll-system, Property 1: Autentikasi Kredensial Valid
// Feature: employee-payroll-system, Property 5: RBAC — Akses Sesuai Peran
describe('login success — redirect per role', function () {
    it('should redirect admin to admin.dashboard', function () {
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

    it('should redirect pemilik_pt to owner.dashboard', function () {
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

    it('should redirect karyawan to karyawan.dashboard', function () {
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

    it('should reset login_attempts and set last_login after successful login', function () {
        $user = User::factory()->admin()->create([
            'email' => 'reset@ipm.test',
            'password' => Hash::make('password123'),
            'login_attempts' => 3,
            'last_login' => null,
        ]);

        $this->post('/login', [
            'email' => 'reset@ipm.test',
            'password' => 'password123',
        ]);

        $user->refresh();
        expect($user->login_attempts)->toBe(0)
            ->and($user->last_login)->not->toBeNull();
    });
});

// ============================================================
// Login gagal → redirect back dengan error
// ============================================================

// Feature: employee-payroll-system, Property 2: Penolakan Kredensial Tidak Valid
describe('login fail — redirect back with error', function () {
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

        // Verifikasi login_attempts bertambah
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

    it('should show locked message when account is locked', function () {
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
// Login 5x gagal → akun terkunci
// ============================================================

// Feature: employee-payroll-system, Property 2: Penolakan Kredensial Tidak Valid
describe('lockout after 5 consecutive failures', function () {
    it('should lock account after 5 consecutive failed login attempts', function () {
        $user = User::factory()->create([
            'email' => 'lockme@ipm.test',
            'password' => Hash::make('correctpass'),
        ]);

        // 5 percobaan gagal berturut-turut
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

        // 5 percobaan gagal → lock
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'locktest@ipm.test',
                'password' => 'wrongpass',
            ]);
        }

        // Coba login dengan password benar → tetap ditolak karena terkunci
        $response = $this->post('/login', [
            'email' => 'locktest@ipm.test',
            'password' => 'correctpass',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    });

    it('should allow login after lockout duration expires', function () {
        $user = User::factory()->create([
            'email' => 'expiredlock@ipm.test',
            'password' => Hash::make('correctpass'),
            'role' => 'admin',
            'login_attempts' => 5,
            'locked_until' => now()->subMinutes(1), // Lock sudah expired
        ]);

        $response = $this->post('/login', [
            'email' => 'expiredlock@ipm.test',
            'password' => 'correctpass',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();
    });
});

// ============================================================
// Logout → session dihapus
// ============================================================

// Feature: employee-payroll-system, Property 3: Logout Menghapus Sesi (Round-Trip)
describe('logout — session destroyed', function () {
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

        // Login
        $this->actingAs($user);
        $this->assertAuthenticated();

        // Logout
        $this->post('/logout');
        $this->assertGuest();

        // Coba akses dashboard → redirect ke login
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect(route('login'));
    });

    it('should invalidate session token after logout (round-trip)', function () {
        $user = User::factory()->admin()->create();

        // Login via HTTP
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password', // default factory password
        ]);
        $this->assertAuthenticated();

        // Logout
        $this->post('/logout');
        $this->assertGuest();

        // Verifikasi tidak bisa akses resource protected
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect(route('login'));
    });
});

// ============================================================
// Login/logout dicatat ke audit log
// ============================================================

// Feature: employee-payroll-system, Property 17: Invariant Audit Log
describe('audit log — login/logout recorded', function () {
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
            ->and($auditLog->model_tipe)->toBe('User')
            ->and($auditLog->data_baru['role'])->toBe('admin');
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
            ->and($auditLog->model_tipe)->toBe('User')
            ->and($auditLog->data_baru['login_attempts'])->toBe(1);
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
            ->and($auditLog->model_id)->toBe($user->id)
            ->and($auditLog->data_baru['email'])->toBe('auditlogout@ipm.test');
    });

    it('should record lockout event in audit log after 5 failed attempts', function () {
        $user = User::factory()->create([
            'email' => 'auditlock@ipm.test',
            'password' => Hash::make('password123'),
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'auditlock@ipm.test',
                'password' => 'wrongpassword',
            ]);
        }

        // Cari audit log yang mencatat lockout (locked=true)
        $lockoutLog = AuditLog::where('jenis_aktivitas', 'login_gagal')
            ->where('model_id', $user->id)
            ->get()
            ->last();

        expect($lockoutLog)->not->toBeNull()
            ->and($lockoutLog->data_baru['locked'])->toBeTrue()
            ->and($lockoutLog->data_baru['login_attempts'])->toBe(5);
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

        // Password di database tidak boleh sama dengan plaintext
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

        // Hash::check harus berhasil untuk password benar
        expect(Hash::check($plainPassword, $rawPassword))->toBeTrue();
        // Hash::check harus gagal untuk password salah
        expect(Hash::check('wrongpassword', $rawPassword))->toBeFalse();
    });

    it('should produce different hashes for same password (bcrypt salt)', function () {
        $plainPassword = 'SamePassword789!';

        $hash1 = Hash::make($plainPassword);
        $hash2 = Hash::make($plainPassword);

        // Bcrypt menggunakan random salt, jadi hash berbeda
        expect($hash1)->not->toBe($hash2);
        // Tapi keduanya valid untuk password yang sama
        expect(Hash::check($plainPassword, $hash1))->toBeTrue()
            ->and(Hash::check($plainPassword, $hash2))->toBeTrue();
    });
});
