<?php

// Feature: employee-payroll-system
// Unit test untuk Model Karyawan — data lengkap karyawan outsourcing.
// Validates: Req 3 (Manajemen Data Karyawan)

use App\Models\Karyawan;
use App\Models\User;
use App\Models\PtKlien;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Karyawan Model — Table & Fillable', function () {
    it('uses karyawan table', function () {
        $karyawan = new Karyawan();
        expect($karyawan->getTable())->toBe('karyawan');
    });

    it('has correct fillable attributes', function () {
        $karyawan = new Karyawan();
        expect($karyawan->getFillable())->toBe([
            'user_id',
            'pt_klien_id',
            'nik',
            'nama_lengkap',
            'tanggal_lahir',
            'alamat',
            'telepon',
            'jabatan',
            'gaji_pokok',
            'tanggal_bergabung',
            'status_aktif',
        ]);
    });
});

describe('Karyawan Model — Casts', function () {
    it('casts tanggal_lahir to date', function () {
        $ptKlien = PtKlien::create([
            'nama' => 'PT Test',
            'alamat' => 'Jl. Test',
            'telepon' => '021-1234567',
            'email' => 'test@pt.co.id',
            'nama_pic' => 'Test PIC',
            'nomor_kontrak' => 'KTR-TEST-001',
            'tgl_mulai' => '2025-01-01',
            'tgl_berakhir' => '2025-12-31',
            'fee_jasa' => 5000000,
        ]);

        $user = User::factory()->create(['role' => 'karyawan']);

        $karyawan = Karyawan::create([
            'user_id' => $user->id,
            'pt_klien_id' => $ptKlien->id,
            'nik' => 'KRY-TEST-001',
            'nama_lengkap' => 'Test Karyawan',
            'tanggal_lahir' => '1990-01-15',
            'alamat' => 'Jl. Test No. 1',
            'telepon' => '081234567890',
            'jabatan' => 'Staff',
            'gaji_pokok' => 4000000,
            'tanggal_bergabung' => '2025-01-01',
            'status_aktif' => true,
        ]);

        expect($karyawan->tanggal_lahir)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
        expect($karyawan->tanggal_bergabung)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
        expect($karyawan->status_aktif)->toBeBool()->toBeTrue();
        expect($karyawan->gaji_pokok)->toBe('4000000.00');
    });
});

describe('Karyawan Model — Relations', function () {
    it('belongs to User', function () {
        $karyawan = new Karyawan();
        expect($karyawan->user())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class);
    });

    it('belongs to PtKlien', function () {
        $karyawan = new Karyawan();
        expect($karyawan->ptKlien())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class);
    });

    it('has many Absensi', function () {
        $karyawan = new Karyawan();
        expect($karyawan->absensi())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
    });

    it('has many SlipGaji', function () {
        $karyawan = new Karyawan();
        expect($karyawan->slipGaji())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
    });
});
