<?php

// Feature: employee-payroll-system
// Unit test untuk Model SlipGaji — hasil perhitungan gaji per karyawan per periode.
// Validates: Req 7 (Perhitungan Gaji), Req 8 (Slip Gaji)

use App\Models\SlipGaji;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('SlipGaji Model — Table & Fillable', function () {
    it('uses slip_gaji table', function () {
        $slip = new SlipGaji();
        expect($slip->getTable())->toBe('slip_gaji');
    });

    it('has correct fillable attributes', function () {
        $slip = new SlipGaji();
        expect($slip->getFillable())->toBe([
            'karyawan_id',
            'periode_id',
            'gaji_pokok',
            'total_tunjangan',
            'total_lembur',
            'jam_lembur',
            'total_potongan',
            'gaji_bersih',
            'status',
        ]);
    });
});

describe('SlipGaji Model — Casts', function () {
    it('casts all decimal fields correctly', function () {
        $slip = new SlipGaji();
        $casts = $slip->getCasts();

        expect($casts['gaji_pokok'])->toBe('decimal:2');
        expect($casts['total_tunjangan'])->toBe('decimal:2');
        expect($casts['total_lembur'])->toBe('decimal:2');
        expect($casts['jam_lembur'])->toBe('decimal:2');
        expect($casts['total_potongan'])->toBe('decimal:2');
        expect($casts['gaji_bersih'])->toBe('decimal:2');
    });
});

describe('SlipGaji Model — Relations', function () {
    it('belongs to Karyawan', function () {
        $slip = new SlipGaji();
        expect($slip->karyawan())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class);
    });

    it('belongs to PeriodePenggajian', function () {
        $slip = new SlipGaji();
        expect($slip->periodePenggajian())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class);
    });

    it('has many KomponenSlipGaji', function () {
        $slip = new SlipGaji();
        expect($slip->komponenSlipGaji())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
    });
});
