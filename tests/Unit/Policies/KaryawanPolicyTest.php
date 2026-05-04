<?php

// Feature: employee-payroll-system, Task 3: Autentikasi & Keamanan
// Unit test untuk KaryawanPolicy — otorisasi CRUD data karyawan
// @see Property 5: RBAC — Akses Sesuai Peran
// @see Property 6: Isolasi Data Karyawan
// @see Req 2.2 (Admin: akses penuh manajemen data)
// @see Req 2.4 (Karyawan: akses data diri sendiri)

use App\Models\Karyawan;
use App\Models\PtKlien;
use App\Models\User;
use App\Policies\KaryawanPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->policy = new KaryawanPolicy();

    $this->admin = User::factory()->admin()->create();
    $this->pemilikPt = User::factory()->pemilikPt()->create();
    $this->karyawanUser = User::factory()->karyawan()->create();

    $this->ptKlien = PtKlien::create([
        'nama' => 'PT Test Klien',
        'alamat' => 'Jl. Test No. 1',
        'telepon' => '021-1234567',
        'email' => 'test@klien.com',
        'nama_pic' => 'PIC Test',
        'nomor_kontrak' => 'KTR-001',
        'tgl_mulai' => now()->subYear(),
        'tgl_berakhir' => now()->addYear(),
        'fee_jasa' => 5000000,
    ]);

    $this->karyawan = Karyawan::create([
        'user_id' => $this->karyawanUser->id,
        'pt_klien_id' => $this->ptKlien->id,
        'nik' => '1234567890123456',
        'nama_lengkap' => 'Test Karyawan',
        'tanggal_lahir' => '1990-01-01',
        'alamat' => 'Jl. Karyawan No. 1',
        'telepon' => '08123456789',
        'jabatan' => 'Staff',
        'gaji_pokok' => 5000000,
        'tanggal_bergabung' => now()->subMonths(6),
        'status_aktif' => true,
    ]);
});

// ============================================================
// Admin — akses penuh: viewAny, view, create, update, delete
// ============================================================

// Feature: employee-payroll-system, Property 5: RBAC — Akses Sesuai Peran
describe('Admin — full CRUD access', function () {
    it('can viewAny karyawan', function () {
        expect($this->policy->viewAny($this->admin))->toBeTrue();
    });

    it('can view a specific karyawan', function () {
        expect($this->policy->view($this->admin, $this->karyawan))->toBeTrue();
    });

    it('can create karyawan', function () {
        expect($this->policy->create($this->admin))->toBeTrue();
    });

    it('can update any karyawan', function () {
        expect($this->policy->update($this->admin, $this->karyawan))->toBeTrue();
    });

    it('can delete any karyawan', function () {
        expect($this->policy->delete($this->admin, $this->karyawan))->toBeTrue();
    });
});

// ============================================================
// Pemilik_PT — semua false (tidak dapat CRUD karyawan)
// ============================================================

// Feature: employee-payroll-system, Property 5: RBAC — Akses Sesuai Peran
describe('Pemilik_PT — no access', function () {
    it('cannot viewAny karyawan', function () {
        expect($this->policy->viewAny($this->pemilikPt))->toBeFalse();
    });

    it('cannot view a specific karyawan', function () {
        expect($this->policy->view($this->pemilikPt, $this->karyawan))->toBeFalse();
    });

    it('cannot create karyawan', function () {
        expect($this->policy->create($this->pemilikPt))->toBeFalse();
    });

    it('cannot update karyawan', function () {
        expect($this->policy->update($this->pemilikPt, $this->karyawan))->toBeFalse();
    });

    it('cannot delete karyawan', function () {
        expect($this->policy->delete($this->pemilikPt, $this->karyawan))->toBeFalse();
    });
});

// ============================================================
// Karyawan — semua false untuk CRUD umum, tapi bisa view/update milik sendiri
// ============================================================

// Feature: employee-payroll-system, Property 5 & 6: RBAC + Isolasi Data Karyawan
describe('Karyawan — self-service only', function () {
    it('cannot viewAny karyawan', function () {
        expect($this->policy->viewAny($this->karyawanUser))->toBeFalse();
    });

    it('can view their own karyawan data', function () {
        expect($this->policy->view($this->karyawanUser, $this->karyawan))->toBeTrue();
    });

    it('cannot view another karyawan data', function () {
        $otherUser = User::factory()->karyawan()->create();
        $otherKaryawan = Karyawan::create([
            'user_id' => $otherUser->id,
            'pt_klien_id' => $this->ptKlien->id,
            'nik' => '9999999999999999',
            'nama_lengkap' => 'Other Karyawan',
            'tanggal_lahir' => '1992-05-05',
            'alamat' => 'Jl. Other',
            'telepon' => '089999999',
            'jabatan' => 'Staff',
            'gaji_pokok' => 4500000,
            'tanggal_bergabung' => now()->subMonths(3),
            'status_aktif' => true,
        ]);

        expect($this->policy->view($this->karyawanUser, $otherKaryawan))->toBeFalse();
    });

    it('cannot create karyawan', function () {
        expect($this->policy->create($this->karyawanUser))->toBeFalse();
    });

    it('can update their own karyawan data', function () {
        expect($this->policy->update($this->karyawanUser, $this->karyawan))->toBeTrue();
    });

    it('cannot update another karyawan data', function () {
        $otherUser = User::factory()->karyawan()->create();
        $otherKaryawan = Karyawan::create([
            'user_id' => $otherUser->id,
            'pt_klien_id' => $this->ptKlien->id,
            'nik' => '8888888888888888',
            'nama_lengkap' => 'Another Karyawan',
            'tanggal_lahir' => '1993-03-03',
            'alamat' => 'Jl. Another',
            'telepon' => '088888888',
            'jabatan' => 'Staff',
            'gaji_pokok' => 4000000,
            'tanggal_bergabung' => now()->subMonths(2),
            'status_aktif' => true,
        ]);

        expect($this->policy->update($this->karyawanUser, $otherKaryawan))->toBeFalse();
    });

    it('cannot delete any karyawan (including own)', function () {
        expect($this->policy->delete($this->karyawanUser, $this->karyawan))->toBeFalse();
    });
});
