<?php

// Feature: employee-payroll-system
// Unit test untuk Model PtKlien — data perusahaan klien.
// Validates: Req 4 (Manajemen Data PT Klien)

use App\Models\PtKlien;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('PtKlien Model — Table & Fillable', function () {
    it('uses pt_klien table', function () {
        $ptKlien = new PtKlien();
        expect($ptKlien->getTable())->toBe('pt_klien');
    });

    it('has correct fillable attributes', function () {
        $ptKlien = new PtKlien();
        expect($ptKlien->getFillable())->toBe([
            'nama',
            'alamat',
            'telepon',
            'email',
            'nama_pic',
            'nomor_kontrak',
            'tgl_mulai',
            'tgl_berakhir',
            'fee_jasa',
        ]);
    });
});

describe('PtKlien Model — Casts', function () {
    it('casts date and decimal fields correctly', function () {
        $ptKlien = PtKlien::create([
            'nama' => 'PT Cast Test',
            'alamat' => 'Jl. Test',
            'telepon' => '021-1234567',
            'email' => 'cast@pt.co.id',
            'nama_pic' => 'PIC Test',
            'nomor_kontrak' => 'KTR-CAST-001',
            'tgl_mulai' => '2025-01-01',
            'tgl_berakhir' => '2025-12-31',
            'fee_jasa' => 5000000.50,
        ]);

        expect($ptKlien->tgl_mulai)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
        expect($ptKlien->tgl_berakhir)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
        expect($ptKlien->fee_jasa)->toBe('5000000.50');
    });
});

describe('PtKlien Model — Relations', function () {
    it('has many Karyawan', function () {
        $ptKlien = new PtKlien();
        expect($ptKlien->karyawan())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
    });

    it('has one KonfigurasiGaji', function () {
        $ptKlien = new PtKlien();
        expect($ptKlien->konfigurasiGaji())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOne::class);
    });

    it('has many Invoice', function () {
        $ptKlien = new PtKlien();
        expect($ptKlien->invoices())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
    });
});
