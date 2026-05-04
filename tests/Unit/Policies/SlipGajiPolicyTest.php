<?php

// Feature: employee-payroll-system, Task 3: Autentikasi & Keamanan
// Unit test untuk SlipGajiPolicy — otorisasi akses slip gaji
// @see Property 5: RBAC — Akses Sesuai Peran
// @see Property 6: Isolasi Data Karyawan
// @see Req 8.5, 8.6, 12.4

use App\Models\Karyawan;
use App\Models\PeriodePenggajian;
use App\Models\PtKlien;
use App\Models\SlipGaji;
use App\Models\User;
use App\Policies\SlipGajiPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->policy = new SlipGajiPolicy();

    $this->admin = User::factory()->admin()->create();
    $this->pemilikPt = User::factory()->pemilikPt()->create();

    // Karyawan A
    $this->karyawanUserA = User::factory()->karyawan()->create();
    // Karyawan B
    $this->karyawanUserB = User::factory()->karyawan()->create();

    $this->ptKlien = PtKlien::create([
        'nama' => 'PT Test',
        'alamat' => 'Jl. Test',
        'telepon' => '021-111',
        'email' => 'test@pt.com',
        'nama_pic' => 'PIC',
        'nomor_kontrak' => 'KTR-001',
        'tgl_mulai' => now()->subYear(),
        'tgl_berakhir' => now()->addYear(),
        'fee_jasa' => 5000000,
    ]);

    $this->karyawanA = Karyawan::create([
        'user_id' => $this->karyawanUserA->id,
        'pt_klien_id' => $this->ptKlien->id,
        'nik' => '1111111111111111',
        'nama_lengkap' => 'Karyawan A',
        'tanggal_lahir' => '1990-01-01',
        'alamat' => 'Jl. A',
        'telepon' => '081111111',
        'jabatan' => 'Staff',
        'gaji_pokok' => 5000000,
        'tanggal_bergabung' => now()->subMonths(6),
        'status_aktif' => true,
    ]);

    $this->karyawanB = Karyawan::create([
        'user_id' => $this->karyawanUserB->id,
        'pt_klien_id' => $this->ptKlien->id,
        'nik' => '2222222222222222',
        'nama_lengkap' => 'Karyawan B',
        'tanggal_lahir' => '1991-02-02',
        'alamat' => 'Jl. B',
        'telepon' => '082222222',
        'jabatan' => 'Staff',
        'gaji_pokok' => 5500000,
        'tanggal_bergabung' => now()->subMonths(3),
        'status_aktif' => true,
    ]);

    $this->periode = PeriodePenggajian::create([
        'bulan' => now()->month,
        'tahun' => now()->year,
        'tanggal_mulai' => now()->startOfMonth(),
        'tanggal_selesai' => now()->endOfMonth(),
        'status' => 'aktif',
    ]);

    $this->slipGajiA = SlipGaji::create([
        'karyawan_id' => $this->karyawanA->id,
        'periode_id' => $this->periode->id,
        'gaji_pokok' => 5000000,
        'total_tunjangan' => 500000,
        'total_lembur' => 200000,
        'jam_lembur' => 10,
        'total_potongan' => 100000,
        'gaji_bersih' => 5600000,
        'status' => 'final',
    ]);

    $this->slipGajiB = SlipGaji::create([
        'karyawan_id' => $this->karyawanB->id,
        'periode_id' => $this->periode->id,
        'gaji_pokok' => 5500000,
        'total_tunjangan' => 600000,
        'total_lembur' => 150000,
        'jam_lembur' => 8,
        'total_potongan' => 0,
        'gaji_bersih' => 6250000,
        'status' => 'final',
    ]);
});

// ============================================================
// Admin — viewAny=true, view=true (semua slip)
// ============================================================

// Feature: employee-payroll-system, Property 5: RBAC — Akses Sesuai Peran
describe('Admin — full access to all slip gaji', function () {
    it('can viewAny slip gaji', function () {
        expect($this->policy->viewAny($this->admin))->toBeTrue();
    });

    it('can view slip gaji A', function () {
        expect($this->policy->view($this->admin, $this->slipGajiA))->toBeTrue();
    });

    it('can view slip gaji B', function () {
        expect($this->policy->view($this->admin, $this->slipGajiB))->toBeTrue();
    });

    it('can download any slip gaji', function () {
        expect($this->policy->download($this->admin, $this->slipGajiA))->toBeTrue()
            ->and($this->policy->download($this->admin, $this->slipGajiB))->toBeTrue();
    });
});

// ============================================================
// Karyawan — view=true (slip milik sendiri), view=false (slip orang lain)
// ============================================================

// Feature: employee-payroll-system, Property 6: Isolasi Data Karyawan
describe('Karyawan — data isolation on slip gaji', function () {
    it('can view their own slip gaji', function () {
        expect($this->policy->view($this->karyawanUserA, $this->slipGajiA))->toBeTrue();
    });

    it('cannot view another karyawan slip gaji', function () {
        expect($this->policy->view($this->karyawanUserA, $this->slipGajiB))->toBeFalse();
    });

    it('can download their own slip gaji', function () {
        expect($this->policy->download($this->karyawanUserA, $this->slipGajiA))->toBeTrue();
    });

    it('cannot download another karyawan slip gaji', function () {
        expect($this->policy->download($this->karyawanUserA, $this->slipGajiB))->toBeFalse();
    });

    it('cannot viewAny slip gaji', function () {
        expect($this->policy->viewAny($this->karyawanUserA))->toBeFalse();
    });

    // Verifikasi isolasi dari kedua sisi
    it('karyawan B can view own slip but not slip A', function () {
        expect($this->policy->view($this->karyawanUserB, $this->slipGajiB))->toBeTrue()
            ->and($this->policy->view($this->karyawanUserB, $this->slipGajiA))->toBeFalse();
    });
});

// ============================================================
// Pemilik_PT — viewAny=false, view=false
// ============================================================

// Feature: employee-payroll-system, Property 5: RBAC — Akses Sesuai Peran
describe('Pemilik_PT — no access to slip gaji', function () {
    it('cannot viewAny slip gaji', function () {
        expect($this->policy->viewAny($this->pemilikPt))->toBeFalse();
    });

    it('cannot view any slip gaji', function () {
        expect($this->policy->view($this->pemilikPt, $this->slipGajiA))->toBeFalse()
            ->and($this->policy->view($this->pemilikPt, $this->slipGajiB))->toBeFalse();
    });

    it('cannot download any slip gaji', function () {
        expect($this->policy->download($this->pemilikPt, $this->slipGajiA))->toBeFalse();
    });
});
