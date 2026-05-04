<?php

// Feature: employee-payroll-system
// Unit test untuk Model KomponenSlipGaji — rincian komponen tunjangan dan potongan.
// Validates: Req 8.2 (Rincian Slip Gaji)

use App\Models\KomponenSlipGaji;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('KomponenSlipGaji Model — Table & Config', function () {
    it('uses komponen_slip_gaji table', function () {
        $komponen = new KomponenSlipGaji();
        expect($komponen->getTable())->toBe('komponen_slip_gaji');
    });

    it('has timestamps disabled', function () {
        $komponen = new KomponenSlipGaji();
        expect($komponen->timestamps)->toBeFalse();
    });

    it('has correct fillable attributes', function () {
        $komponen = new KomponenSlipGaji();
        expect($komponen->getFillable())->toBe([
            'slip_gaji_id',
            'tipe',
            'nama_komponen',
            'nilai',
        ]);
    });
});

describe('KomponenSlipGaji Model — Casts', function () {
    it('casts nilai to decimal:2', function () {
        $komponen = new KomponenSlipGaji();
        $casts = $komponen->getCasts();
        expect($casts['nilai'])->toBe('decimal:2');
    });

    it('casts created_at to datetime', function () {
        $komponen = new KomponenSlipGaji();
        $casts = $komponen->getCasts();
        expect($casts['created_at'])->toBe('datetime');
    });
});

describe('KomponenSlipGaji Model — Relations', function () {
    it('belongs to SlipGaji', function () {
        $komponen = new KomponenSlipGaji();
        expect($komponen->slipGaji())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class);
    });
});
