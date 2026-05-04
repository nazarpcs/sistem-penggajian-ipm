<?php

// Feature: employee-payroll-system
// Unit test untuk Model PeriodePenggajian — rentang waktu satu bulan untuk perhitungan gaji.
// Validates: Req 6 (Rekap dan Pengolahan Absensi)

use App\Models\PeriodePenggajian;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('PeriodePenggajian Model — Table & Fillable', function () {
    it('uses periode_penggajian table', function () {
        $periode = new PeriodePenggajian();
        expect($periode->getTable())->toBe('periode_penggajian');
    });

    it('has correct fillable attributes', function () {
        $periode = new PeriodePenggajian();
        expect($periode->getFillable())->toBe([
            'bulan',
            'tahun',
            'tanggal_mulai',
            'tanggal_selesai',
            'status',
        ]);
    });
});

describe('PeriodePenggajian Model — Casts', function () {
    it('casts date and integer fields correctly', function () {
        $periode = PeriodePenggajian::create([
            'bulan' => 6,
            'tahun' => 2025,
            'tanggal_mulai' => '2025-06-01',
            'tanggal_selesai' => '2025-06-30',
            'status' => 'aktif',
        ]);

        expect($periode->tanggal_mulai)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
        expect($periode->tanggal_selesai)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
        expect($periode->bulan)->toBeInt()->toBe(6);
        expect($periode->tahun)->toBeInt()->toBe(2025);
    });
});

describe('PeriodePenggajian Model — Relations', function () {
    it('has many SlipGaji', function () {
        $periode = new PeriodePenggajian();
        expect($periode->slipGaji())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
    });

    it('has many Invoice', function () {
        $periode = new PeriodePenggajian();
        expect($periode->invoices())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
    });
});
