<?php

// Feature: employee-payroll-system, Property 13: Kebenaran Rumus Perhitungan Gaji
// Unit test untuk DTO KomponenGaji — input data untuk perhitungan gaji.
// Validates: Req 7 (Perhitungan Gaji Otomatis)

use App\Domain\Payroll\KomponenGaji;

describe('KomponenGaji DTO — Instantiation', function () {
    it('can be instantiated with all parameters', function () {
        $komponen = new KomponenGaji(
            gajiPokok: 4000000,
            tunjangan: ['Transport' => 500000, 'Makan' => 300000],
            jamLembur: 10.5,
            tarifLemburPerJam: 25000,
            hariAlpha: 2,
            potonganPerHari: 150000,
        );

        expect($komponen)->toBeInstanceOf(KomponenGaji::class);
    });

    it('can be instantiated with only gajiPokok (defaults for rest)', function () {
        $komponen = new KomponenGaji(gajiPokok: 5000000);

        expect($komponen->gajiPokok)->toBe(5000000);
        expect($komponen->tunjangan)->toBe([]);
        expect($komponen->jamLembur)->toBe(0.0);
        expect($komponen->tarifLemburPerJam)->toBe(0);
        expect($komponen->hariAlpha)->toBe(0);
        expect($komponen->potonganPerHari)->toBe(0);
    });
});

describe('KomponenGaji DTO — Readonly Properties', function () {
    it('has all properties accessible and readonly', function () {
        $tunjangan = ['Transport' => 500000, 'Makan' => 300000];
        $komponen = new KomponenGaji(
            gajiPokok: 4000000,
            tunjangan: $tunjangan,
            jamLembur: 10.5,
            tarifLemburPerJam: 25000,
            hariAlpha: 2,
            potonganPerHari: 150000,
        );

        expect($komponen->gajiPokok)->toBe(4000000);
        expect($komponen->tunjangan)->toBe($tunjangan);
        expect($komponen->jamLembur)->toBe(10.5);
        expect($komponen->tarifLemburPerJam)->toBe(25000);
        expect($komponen->hariAlpha)->toBe(2);
        expect($komponen->potonganPerHari)->toBe(150000);
    });

    it('is a final readonly class', function () {
        $reflection = new ReflectionClass(KomponenGaji::class);
        expect($reflection->isFinal())->toBeTrue();
        expect($reflection->isReadOnly())->toBeTrue();
    });
});

describe('KomponenGaji DTO — Default Values', function () {
    it('has empty array as default for tunjangan', function () {
        $komponen = new KomponenGaji(gajiPokok: 3000000);
        expect($komponen->tunjangan)->toBeArray()->toBeEmpty();
    });

    it('has 0 as default for jamLembur', function () {
        $komponen = new KomponenGaji(gajiPokok: 3000000);
        expect($komponen->jamLembur)->toBe(0.0);
    });

    it('has 0 as default for tarifLemburPerJam', function () {
        $komponen = new KomponenGaji(gajiPokok: 3000000);
        expect($komponen->tarifLemburPerJam)->toBe(0);
    });

    it('has 0 as default for hariAlpha', function () {
        $komponen = new KomponenGaji(gajiPokok: 3000000);
        expect($komponen->hariAlpha)->toBe(0);
    });

    it('has 0 as default for potonganPerHari', function () {
        $komponen = new KomponenGaji(gajiPokok: 3000000);
        expect($komponen->potonganPerHari)->toBe(0);
    });
});
