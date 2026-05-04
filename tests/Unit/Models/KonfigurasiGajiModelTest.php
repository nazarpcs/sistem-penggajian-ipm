<?php

// Feature: employee-payroll-system
// Unit test untuk Model KonfigurasiGaji — aturan perhitungan gaji per PT Klien.
// Validates: Req 4.5, 4.6 (Konfigurasi Gaji PT Klien)

use App\Models\KonfigurasiGaji;
use App\Models\PtKlien;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('KonfigurasiGaji Model — Table & Fillable', function () {
    it('uses konfigurasi_gaji table', function () {
        $config = new KonfigurasiGaji();
        expect($config->getTable())->toBe('konfigurasi_gaji');
    });

    it('has correct fillable attributes', function () {
        $config = new KonfigurasiGaji();
        expect($config->getFillable())->toBe([
            'pt_klien_id',
            'gaji_pokok_default',
            'jam_kerja_normal',
            'tarif_lembur_per_jam',
            'potongan_per_hari',
            'komponen_tunjangan',
        ]);
    });
});

describe('KonfigurasiGaji Model — Casts', function () {
    it('casts komponen_tunjangan to array', function () {
        $ptKlien = PtKlien::create([
            'nama' => 'PT Config Test',
            'alamat' => 'Jl. Test',
            'telepon' => '021-1234567',
            'email' => 'config@pt.co.id',
            'nama_pic' => 'PIC Test',
            'nomor_kontrak' => 'KTR-CFG-001',
            'tgl_mulai' => '2025-01-01',
            'tgl_berakhir' => '2025-12-31',
            'fee_jasa' => 5000000,
        ]);

        $config = KonfigurasiGaji::create([
            'pt_klien_id' => $ptKlien->id,
            'gaji_pokok_default' => 4000000,
            'jam_kerja_normal' => 8.00,
            'tarif_lembur_per_jam' => 25000,
            'potongan_per_hari' => 150000,
            'komponen_tunjangan' => [
                ['nama' => 'Transport', 'nilai' => 500000],
                ['nama' => 'Makan', 'nilai' => 300000],
            ],
        ]);

        $config->refresh();

        expect($config->komponen_tunjangan)->toBeArray();
        expect($config->komponen_tunjangan)->toHaveCount(2);
        expect($config->komponen_tunjangan[0]['nama'])->toBe('Transport');
        expect($config->komponen_tunjangan[0]['nilai'])->toBe(500000);
        expect($config->gaji_pokok_default)->toBe('4000000.00');
        expect($config->tarif_lembur_per_jam)->toBe('25000.00');
        expect($config->potongan_per_hari)->toBe('150000.00');
    });
});

describe('KonfigurasiGaji Model — Relations', function () {
    it('belongs to PtKlien', function () {
        $config = new KonfigurasiGaji();
        expect($config->ptKlien())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class);
    });
});
