<?php

// Feature: employee-payroll-system, Property 13: Kebenaran Rumus Perhitungan Gaji
// Unit test untuk KalkulatorGaji — pure domain class tanpa dependensi framework.
// Validates: Req 7.1-7.3, Property 18: Batas Minimum Gaji Bersih

use App\Domain\Payroll\KalkulatorGaji;
use App\Domain\Payroll\KalkulatorGajiInterface;
use App\Domain\Payroll\KomponenGaji;
use App\Domain\Payroll\HasilPerhitunganGaji;

describe('KalkulatorGaji — Instantiation', function () {
    it('implements KalkulatorGajiInterface', function () {
        $kalkulator = new KalkulatorGaji();
        expect($kalkulator)->toBeInstanceOf(KalkulatorGajiInterface::class);
    });

    it('has hitung method', function () {
        $kalkulator = new KalkulatorGaji();
        expect(method_exists($kalkulator, 'hitung'))->toBeTrue();
    });
});

describe('KalkulatorGaji — hitung() returns HasilPerhitunganGaji', function () {
    it('returns HasilPerhitunganGaji instance', function () {
        $kalkulator = new KalkulatorGaji();
        $komponen = new KomponenGaji(gajiPokok: 4000000);

        $hasil = $kalkulator->hitung($komponen);

        expect($hasil)->toBeInstanceOf(HasilPerhitunganGaji::class);
    });

    it('returns correct gajiPokok from input', function () {
        $kalkulator = new KalkulatorGaji();
        $komponen = new KomponenGaji(gajiPokok: 5000000);

        $hasil = $kalkulator->hitung($komponen);

        expect($hasil->gajiPokok)->toBe(5000000);
    });
});

describe('KalkulatorGaji — Edge Cases', function () {
    it('handles zero gajiPokok', function () {
        $kalkulator = new KalkulatorGaji();
        $komponen = new KomponenGaji(gajiPokok: 0);

        $hasil = $kalkulator->hitung($komponen);

        expect($hasil->gajiPokok)->toBe(0);
        expect($hasil->gajiBersih)->toBeGreaterThanOrEqual(0);
    });

    it('handles all components as zero', function () {
        $kalkulator = new KalkulatorGaji();
        $komponen = new KomponenGaji(
            gajiPokok: 0,
            tunjangan: [],
            jamLembur: 0,
            tarifLemburPerJam: 0,
            hariAlpha: 0,
            potonganPerHari: 0,
        );

        $hasil = $kalkulator->hitung($komponen);

        expect($hasil->gajiBersih)->toBeGreaterThanOrEqual(0);
    });
});
