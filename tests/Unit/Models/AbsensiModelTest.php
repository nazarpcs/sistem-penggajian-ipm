<?php

// Feature: employee-payroll-system
// Unit test untuk Model Absensi — catatan kehadiran harian karyawan.
// Validates: Req 5 (Input dan Upload Absensi), Property 12: Uniqueness Absensi

use App\Models\Absensi;
use App\Models\Karyawan;
use App\Models\PtKlien;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Absensi Model — Table & Fillable', function () {
    it('uses absensi table', function () {
        $absensi = new Absensi();
        expect($absensi->getTable())->toBe('absensi');
    });

    it('has correct fillable attributes', function () {
        $absensi = new Absensi();
        expect($absensi->getFillable())->toBe([
            'karyawan_id',
            'tanggal',
            'status_kehadiran',
            'jam_masuk',
            'jam_keluar',
            'jam_lembur',
            'keterangan',
        ]);
    });
});

describe('Absensi Model — Casts', function () {
    it('casts tanggal to date and jam_lembur to decimal', function () {
        $ptKlien = PtKlien::create([
            'nama' => 'PT Absensi Test',
            'alamat' => 'Jl. Test',
            'telepon' => '021-1234567',
            'email' => 'absensi@pt.co.id',
            'nama_pic' => 'PIC',
            'nomor_kontrak' => 'KTR-ABS-001',
            'tgl_mulai' => '2025-01-01',
            'tgl_berakhir' => '2025-12-31',
            'fee_jasa' => 5000000,
        ]);

        $user = User::factory()->create(['role' => 'karyawan']);

        $karyawan = Karyawan::create([
            'user_id' => $user->id,
            'pt_klien_id' => $ptKlien->id,
            'nik' => 'KRY-ABS-001',
            'nama_lengkap' => 'Test Absensi',
            'tanggal_lahir' => '1990-01-01',
            'alamat' => 'Jl. Test',
            'telepon' => '081234567890',
            'jabatan' => 'Staff',
            'gaji_pokok' => 4000000,
            'tanggal_bergabung' => '2025-01-01',
            'status_aktif' => true,
        ]);

        $absensi = Absensi::create([
            'karyawan_id' => $karyawan->id,
            'tanggal' => '2025-06-15',
            'status_kehadiran' => 'Hadir',
            'jam_masuk' => '08:00',
            'jam_keluar' => '17:00',
            'jam_lembur' => 1.50,
        ]);

        expect($absensi->tanggal)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
        expect($absensi->jam_lembur)->toBe('1.50');
    });
});

describe('Absensi Model — Relations', function () {
    it('belongs to Karyawan', function () {
        $absensi = new Absensi();
        expect($absensi->karyawan())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class);
    });
});
