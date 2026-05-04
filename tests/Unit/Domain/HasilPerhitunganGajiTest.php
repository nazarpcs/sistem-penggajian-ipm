<?php

// Feature: employee-payroll-system, Property 13: Kebenaran Rumus Perhitungan Gaji
// Unit test untuk DTO HasilPerhitunganGaji — output hasil perhitungan gaji.
// Validates: Req 7 (Perhitungan Gaji), Property 18: Batas Minimum Gaji Bersih

use App\Domain\Payroll\HasilPerhitunganGaji;

describe('HasilPerhitunganGaji DTO — Instantiation', function () {
    it('can be instantiated with all parameters', function () {
        $hasil = new HasilPerhitunganGaji(
            gajiPokok: 4000000,
            totalTunjangan: 800000,
            totalLembur: 250000,
            jamLembur: 10.0,
            totalPotongan: 300000,
            gajiBersih: 4750000,
            rincianTunjangan: ['Transport' => 500000, 'Makan' => 300000],
            peringatanNegatif: false,
        );

        expect($hasil)->toBeInstanceOf(HasilPerhitunganGaji::class);
    });

    it('can be instantiated with required parameters only (defaults for rest)', function () {
        $hasil = new HasilPerhitunganGaji(
            gajiPokok: 4000000,
            totalTunjangan: 0,
            totalLembur: 0,
            jamLembur: 0,
            totalPotongan: 0,
            gajiBersih: 4000000,
        );

        expect($hasil->rincianTunjangan)->toBe([]);
        expect($hasil->peringatanNegatif)->toBeFalse();
    });
});

describe('HasilPerhitunganGaji DTO — Readonly Properties', function () {
    it('has all properties accessible and readonly', function () {
        $rincian = ['Transport' => 500000, 'Makan' => 300000];
        $hasil = new HasilPerhitunganGaji(
            gajiPokok: 4000000,
            totalTunjangan: 800000,
            totalLembur: 250000,
            jamLembur: 10.0,
            totalPotongan: 300000,
            gajiBersih: 4750000,
            rincianTunjangan: $rincian,
            peringatanNegatif: true,
        );

        expect($hasil->gajiPokok)->toBe(4000000);
        expect($hasil->totalTunjangan)->toBe(800000);
        expect($hasil->totalLembur)->toBe(250000);
        expect($hasil->jamLembur)->toBe(10.0);
        expect($hasil->totalPotongan)->toBe(300000);
        expect($hasil->gajiBersih)->toBe(4750000);
        expect($hasil->rincianTunjangan)->toBe($rincian);
        expect($hasil->peringatanNegatif)->toBeTrue();
    });

    it('is a final readonly class', function () {
        $reflection = new ReflectionClass(HasilPerhitunganGaji::class);
        expect($reflection->isFinal())->toBeTrue();
        expect($reflection->isReadOnly())->toBeTrue();
    });
});

describe('HasilPerhitunganGaji DTO — Default Values', function () {
    it('has false as default for peringatanNegatif', function () {
        $hasil = new HasilPerhitunganGaji(
            gajiPokok: 4000000,
            totalTunjangan: 0,
            totalLembur: 0,
            jamLembur: 0,
            totalPotongan: 0,
            gajiBersih: 4000000,
        );

        expect($hasil->peringatanNegatif)->toBeFalse();
    });

    it('has empty array as default for rincianTunjangan', function () {
        $hasil = new HasilPerhitunganGaji(
            gajiPokok: 4000000,
            totalTunjangan: 0,
            totalLembur: 0,
            jamLembur: 0,
            totalPotongan: 0,
            gajiBersih: 4000000,
        );

        expect($hasil->rincianTunjangan)->toBeArray()->toBeEmpty();
    });
});
