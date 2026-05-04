<?php

// Feature: employee-payroll-system, Task 3: Autentikasi & Keamanan
// Unit test untuk AuthService — logika login, lockout, session management
// @see Property 1: Autentikasi Kredensial Valid
// @see Property 2: Penolakan Kredensial Tidak Valid
// @see Property 9: Sinkronisasi Status Karyawan dan Akun Login
// @see Property 17: Invariant Audit Log

use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->authService = new AuthService();
});

// ============================================================
// attemptLogin() — Kredensial Valid
// ============================================================

// Feature: employee-payroll-system, Property 1: Autentikasi Kredensial Valid
describe('attemptLogin — valid credentials', function () {
    it('should return success=true and user when credentials are valid', function () {
        $user = User::factory()->admin()->create([
            'email' => 'admin@ipm.test',
            'password' => bcrypt('rahasia123'),
        ]);

        $result = $this->authService->attemptLogin('admin@ipm.test', 'rahasia123');

        expect($result['success'])->toBeTrue()
            ->and($result['user'])->toBeInstanceOf(User::class)
            ->and($result['user']->id)->toBe($user->id)
            ->and($result['message'])->toBe('Login berhasil.');
    });

    it('should return success for each valid role (admin, pemilik_pt, karyawan)', function () {
        $roles = ['admin', 'pemilik_pt', 'karyawan'];

        foreach ($roles as $role) {
            $user = User::factory()->create([
                'password' => bcrypt('pass123'),
                'role' => $role,
            ]);

            $result = $this->authService->attemptLogin($user->email, 'pass123');

            expect($result['success'])->toBeTrue()
                ->and($result['user']->role)->toBe($role);
        }
    });

    it('should allow login after lock duration has expired', function () {
        $user = User::factory()->create([
            'email' => 'expired-lock@ipm.test',
            'password' => bcrypt('benar123'),
            'login_attempts' => 5,
            'locked_until' => now()->subMinutes(1), // Lock sudah expired
        ]);

        $result = $this->authService->attemptLogin('expired-lock@ipm.test', 'benar123');

        expect($result['success'])->toBeTrue()
            ->and($result['user']->id)->toBe($user->id);
    });
});

// ============================================================
// attemptLogin() — Kredensial Tidak Valid
// ============================================================

// Feature: employee-payroll-system, Property 2: Penolakan Kredensial Tidak Valid
describe('attemptLogin — invalid credentials', function () {
    it('should return success=false when email is not registered', function () {
        $result = $this->authService->attemptLogin('tidakada@ipm.test', 'password');

        expect($result['success'])->toBeFalse()
            ->and($result['user'])->toBeNull()
            ->and($result['message'])->toBe('Email atau password salah.');
    });

    it('should return success=false and increment login_attempts when password is wrong', function () {
        $user = User::factory()->create([
            'email' => 'user@ipm.test',
            'password' => bcrypt('benar123'),
        ]);

        $result = $this->authService->attemptLogin('user@ipm.test', 'salah999');

        expect($result['success'])->toBeFalse()
            ->and($result['user'])->toBeNull();

        $user->refresh();
        expect($user->login_attempts)->toBe(1);
    });

    it('should show remaining attempts in message after failed login', function () {
        User::factory()->create([
            'email' => 'counter@ipm.test',
            'password' => bcrypt('benar123'),
        ]);

        $result = $this->authService->attemptLogin('counter@ipm.test', 'salah');

        // Setelah 1 gagal, sisa percobaan = 5 - 1 = 4
        expect($result['message'])->toContain('Sisa percobaan: 4');
    });

    it('should decrement remaining attempts progressively', function () {
        User::factory()->create([
            'email' => 'progressive@ipm.test',
            'password' => bcrypt('benar123'),
        ]);

        for ($i = 1; $i <= 4; $i++) {
            $result = $this->authService->attemptLogin('progressive@ipm.test', 'salah');
            $expected = 5 - $i;
            expect($result['message'])->toContain("Sisa percobaan: {$expected}");
        }
    });
});

// ============================================================
// attemptLogin() — Akun Terkunci
// ============================================================

// Feature: employee-payroll-system, Property 2: Penolakan Kredensial Tidak Valid
describe('attemptLogin — locked account', function () {
    it('should return success=false with locked message when account is locked', function () {
        User::factory()->locked()->create([
            'email' => 'locked@ipm.test',
            'password' => bcrypt('benar123'),
        ]);

        $result = $this->authService->attemptLogin('locked@ipm.test', 'benar123');

        expect($result['success'])->toBeFalse()
            ->and($result['message'])->toContain('terkunci');
    });

    it('should lock account and return lockout message on 5th failed attempt', function () {
        $user = User::factory()->create([
            'email' => 'lockme@ipm.test',
            'password' => bcrypt('benar123'),
            'login_attempts' => 4,
        ]);

        $result = $this->authService->attemptLogin('lockme@ipm.test', 'salah');

        expect($result['success'])->toBeFalse()
            ->and($result['message'])->toContain('terkunci selama 15 menit');

        $user->refresh();
        expect($user->login_attempts)->toBe(5)
            ->and($user->locked_until)->not->toBeNull()
            ->and($user->locked_until->isFuture())->toBeTrue();
    });
});

// ============================================================
// attemptLogin() — Akun Tidak Aktif
// ============================================================

// Feature: employee-payroll-system, Property 9: Sinkronisasi Status Karyawan dan Akun Login
describe('attemptLogin — inactive account', function () {
    it('should return success=false when account is inactive', function () {
        User::factory()->inactive()->create([
            'email' => 'inactive@ipm.test',
            'password' => bcrypt('benar123'),
        ]);

        $result = $this->authService->attemptLogin('inactive@ipm.test', 'benar123');

        expect($result['success'])->toBeFalse()
            ->and($result['message'])->toContain('tidak aktif');
    });

    it('should check active status before checking password (no login_attempts increment)', function () {
        $user = User::factory()->inactive()->create([
            'email' => 'inactive2@ipm.test',
            'password' => bcrypt('benar123'),
        ]);

        // Bahkan dengan password salah, pesan tetap "tidak aktif"
        $result = $this->authService->attemptLogin('inactive2@ipm.test', 'salah');

        expect($result['success'])->toBeFalse()
            ->and($result['message'])->toContain('tidak aktif');

        // login_attempts tidak bertambah karena pengecekan berhenti di is_active
        $user->refresh();
        expect($user->login_attempts)->toBe(0);
    });
});

// ============================================================
// handleFailedLogin() — Increment login_attempts
// ============================================================

// Feature: employee-payroll-system, Property 2: Penolakan Kredensial Tidak Valid
describe('handleFailedLogin', function () {
    it('should increment login_attempts on failed login', function () {
        $user = User::factory()->create(['login_attempts' => 2]);

        $this->authService->handleFailedLogin($user);

        $user->refresh();
        expect($user->login_attempts)->toBe(3);
    });

    it('should lock account after 5 failed login attempts (locked_until set 15 minutes ahead)', function () {
        $user = User::factory()->create(['login_attempts' => 4]);

        $this->authService->handleFailedLogin($user);

        $user->refresh();
        expect($user->login_attempts)->toBe(5)
            ->and($user->locked_until)->not->toBeNull()
            ->and($user->locked_until->isFuture())->toBeTrue();

        // Verifikasi durasi lockout ~15 menit
        $diffMinutes = (int) now()->diffInMinutes($user->locked_until, false);
        expect($diffMinutes)->toBeGreaterThanOrEqual(14)
            ->and($diffMinutes)->toBeLessThanOrEqual(15);
    });

    it('should not lock account when attempts are below threshold', function () {
        $user = User::factory()->create(['login_attempts' => 3]);

        $this->authService->handleFailedLogin($user);

        $user->refresh();
        expect($user->login_attempts)->toBe(4)
            ->and($user->locked_until)->toBeNull();
    });

    it('should create audit log entry on failed login', function () {
        $user = User::factory()->create();

        $this->authService->handleFailedLogin($user);

        $auditLog = AuditLog::where('jenis_aktivitas', 'login_gagal')
            ->where('model_id', $user->id)
            ->first();

        expect($auditLog)->not->toBeNull()
            ->and($auditLog->model_tipe)->toBe('User')
            ->and($auditLog->data_baru)->toHaveKey('email')
            ->and($auditLog->data_baru)->toHaveKey('login_attempts')
            ->and($auditLog->data_baru)->toHaveKey('locked');
    });

    it('should record locked=true in audit log when lockout threshold reached', function () {
        $user = User::factory()->create(['login_attempts' => 4]);

        $this->authService->handleFailedLogin($user);

        $auditLog = AuditLog::where('jenis_aktivitas', 'login_gagal')
            ->where('model_id', $user->id)
            ->latest('id')
            ->first();

        expect($auditLog->data_baru['locked'])->toBeTrue()
            ->and($auditLog->data_baru['login_attempts'])->toBe(5);
    });
});

// ============================================================
// handleSuccessfulLogin() — Reset login_attempts dan set last_login
// ============================================================

// Feature: employee-payroll-system, Property 1: Autentikasi Kredensial Valid
describe('handleSuccessfulLogin', function () {
    it('should reset login_attempts to 0 and set last_login', function () {
        $user = User::factory()->create([
            'login_attempts' => 3,
            'locked_until' => now()->subMinutes(5),
            'last_login' => null,
        ]);

        $this->authService->handleSuccessfulLogin($user);

        $user->refresh();
        expect($user->login_attempts)->toBe(0)
            ->and($user->locked_until)->toBeNull()
            ->and($user->last_login)->not->toBeNull();
    });

    it('should clear locked_until on successful login', function () {
        $user = User::factory()->create([
            'login_attempts' => 5,
            'locked_until' => now()->subMinutes(1), // expired lock
        ]);

        $this->authService->handleSuccessfulLogin($user);

        $user->refresh();
        expect($user->locked_until)->toBeNull();
    });

    it('should create audit log entry on successful login', function () {
        $user = User::factory()->admin()->create();

        $this->authService->handleSuccessfulLogin($user);

        $auditLog = AuditLog::where('jenis_aktivitas', 'login')
            ->where('model_id', $user->id)
            ->first();

        expect($auditLog)->not->toBeNull()
            ->and($auditLog->model_tipe)->toBe('User')
            ->and($auditLog->data_baru)->toHaveKey('email')
            ->and($auditLog->data_baru)->toHaveKey('role')
            ->and($auditLog->data_baru['role'])->toBe('admin');
    });
});

// ============================================================
// isAccountLocked()
// ============================================================

// Feature: employee-payroll-system, Property 2: Penolakan Kredensial Tidak Valid
describe('isAccountLocked', function () {
    it('should return true when locked_until is in the future', function () {
        $user = User::factory()->locked()->create();

        expect($this->authService->isAccountLocked($user))->toBeTrue();
    });

    it('should return false when locked_until is in the past', function () {
        $user = User::factory()->create([
            'locked_until' => now()->subMinutes(5),
        ]);

        expect($this->authService->isAccountLocked($user))->toBeFalse();
    });

    it('should return false when locked_until is null', function () {
        $user = User::factory()->create(['locked_until' => null]);

        expect($this->authService->isAccountLocked($user))->toBeFalse();
    });
});

// ============================================================
// isAccountActive()
// ============================================================

// Feature: employee-payroll-system, Property 9: Sinkronisasi Status Karyawan dan Akun Login
describe('isAccountActive', function () {
    it('should return false when is_active is false', function () {
        $user = User::factory()->inactive()->create();

        expect($this->authService->isAccountActive($user))->toBeFalse();
    });

    it('should return true when is_active is true', function () {
        $user = User::factory()->create(['is_active' => true]);

        expect($this->authService->isAccountActive($user))->toBeTrue();
    });
});

// ============================================================
// getDashboardRoute()
// ============================================================

// Feature: employee-payroll-system, Property 5: RBAC — Akses Sesuai Peran
describe('getDashboardRoute', function () {
    it('should return admin.dashboard route for admin role', function () {
        $user = User::factory()->admin()->create();

        expect($this->authService->getDashboardRoute($user))->toBe('admin.dashboard');
    });

    it('should return owner.dashboard route for pemilik_pt role', function () {
        $user = User::factory()->pemilikPt()->create();

        expect($this->authService->getDashboardRoute($user))->toBe('owner.dashboard');
    });

    it('should return karyawan.dashboard route for karyawan role', function () {
        $user = User::factory()->karyawan()->create();

        expect($this->authService->getDashboardRoute($user))->toBe('karyawan.dashboard');
    });

    it('should return login route for unknown role', function () {
        $user = User::factory()->create();
        $user->role = 'unknown';

        expect($this->authService->getDashboardRoute($user))->toBe('login');
    });
});
