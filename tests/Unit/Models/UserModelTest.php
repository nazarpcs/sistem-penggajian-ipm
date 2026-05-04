<?php

// Feature: employee-payroll-system
// Unit test untuk Model User — akun login untuk semua pengguna sistem.
// Validates: Req 1 (Autentikasi), Req 2 (RBAC)

use App\Models\User;
use App\Models\Karyawan;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('User Model — Fillable', function () {
    it('has correct fillable attributes', function () {
        $user = new User();
        expect($user->getFillable())->toBe([
            'name',
            'email',
            'password',
            'role',
            'is_active',
            'locked_until',
            'login_attempts',
            'last_login',
        ]);
    });
});

describe('User Model — Hidden', function () {
    it('has correct hidden attributes', function () {
        $user = new User();
        expect($user->getHidden())->toBe([
            'password',
            'remember_token',
        ]);
    });
});

describe('User Model — Casts', function () {
    it('casts is_active to boolean', function () {
        $user = User::factory()->create(['is_active' => 1]);
        expect($user->is_active)->toBeBool()->toBeTrue();
    });

    it('casts locked_until to datetime', function () {
        $user = User::factory()->create(['locked_until' => '2025-06-15 10:00:00']);
        expect($user->locked_until)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    });

    it('casts last_login to datetime', function () {
        $user = User::factory()->create(['last_login' => now()]);
        expect($user->last_login)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    });

    it('casts password as hashed', function () {
        $user = User::factory()->create(['password' => 'plaintext123']);
        // Password harus di-hash, bukan plaintext
        expect($user->password)->not->toBe('plaintext123');
        expect(password_verify('plaintext123', $user->password))->toBeTrue();
    });
});

describe('User Model — Role Methods', function () {
    // Feature: employee-payroll-system, Property 5: RBAC — Akses Sesuai Peran
    it('isAdmin() returns true when role is admin', function () {
        $user = User::factory()->create(['role' => 'admin']);
        expect($user->isAdmin())->toBeTrue();
        expect($user->isPemilikPt())->toBeFalse();
        expect($user->isKaryawan())->toBeFalse();
    });

    it('isPemilikPt() returns true when role is pemilik_pt', function () {
        $user = User::factory()->create(['role' => 'pemilik_pt']);
        expect($user->isPemilikPt())->toBeTrue();
        expect($user->isAdmin())->toBeFalse();
        expect($user->isKaryawan())->toBeFalse();
    });

    it('isKaryawan() returns true when role is karyawan', function () {
        $user = User::factory()->create(['role' => 'karyawan']);
        expect($user->isKaryawan())->toBeTrue();
        expect($user->isAdmin())->toBeFalse();
        expect($user->isPemilikPt())->toBeFalse();
    });
});

describe('User Model — isLocked()', function () {
    // Feature: employee-payroll-system, Property 2: Penolakan Kredensial Tidak Valid
    it('returns true when locked_until is in the future', function () {
        $user = User::factory()->create(['locked_until' => now()->addMinutes(15)]);
        expect($user->isLocked())->toBeTrue();
    });

    it('returns false when locked_until is null', function () {
        $user = User::factory()->create(['locked_until' => null]);
        expect($user->isLocked())->toBeFalse();
    });

    it('returns false when locked_until is in the past', function () {
        $user = User::factory()->create(['locked_until' => now()->subMinutes(1)]);
        expect($user->isLocked())->toBeFalse();
    });
});

describe('User Model — Relations', function () {
    it('has one karyawan relation defined', function () {
        $user = new User();
        expect($user->karyawan())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOne::class);
    });
});
