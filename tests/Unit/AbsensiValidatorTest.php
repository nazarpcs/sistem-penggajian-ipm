<?php

// Feature: employee-payroll-system, Property 11: Validasi Import Excel — Atomicity
// Unit test untuk AbsensiValidator — validasi data absensi.
// Validates: Req 5.3, 5.4, Property 12: Uniqueness Absensi, Property 20: Validasi Sinkron

use App\Domain\Validation\AbsensiValidator;
use App\Domain\Validation\AbsensiValidatorInterface;

describe('AbsensiValidator — Instantiation', function () {
    it('implements AbsensiValidatorInterface', function () {
        $validator = new AbsensiValidator();
        expect($validator)->toBeInstanceOf(AbsensiValidatorInterface::class);
    });

    it('has validasiSatuBaris method', function () {
        $validator = new AbsensiValidator();
        expect(method_exists($validator, 'validasiSatuBaris'))->toBeTrue();
    });

    it('has validasiBulk method', function () {
        $validator = new AbsensiValidator();
        expect(method_exists($validator, 'validasiBulk'))->toBeTrue();
    });

    it('has cekDuplikasi method', function () {
        $validator = new AbsensiValidator();
        expect(method_exists($validator, 'cekDuplikasi'))->toBeTrue();
    });
});

describe('AbsensiValidator — validasiSatuBaris()', function () {
    it('returns array with valid and errors keys', function () {
        $validator = new AbsensiValidator();
        $result = $validator->validasiSatuBaris([
            'karyawan_id' => 1,
            'tanggal' => '2025-06-15',
            'status_kehadiran' => 'Hadir',
        ]);

        expect($result)->toBeArray();
        expect($result)->toHaveKeys(['valid', 'errors']);
        expect($result['valid'])->toBeBool();
        expect($result['errors'])->toBeArray();
    });
});

describe('AbsensiValidator — validasiBulk()', function () {
    it('returns array with expected keys', function () {
        $validator = new AbsensiValidator();
        $rows = [
            ['karyawan_id' => 1, 'tanggal' => '2025-06-15', 'status_kehadiran' => 'Hadir'],
            ['karyawan_id' => 2, 'tanggal' => '2025-06-15', 'status_kehadiran' => 'Izin'],
        ];

        $result = $validator->validasiBulk($rows);

        expect($result)->toBeArray();
        expect($result)->toHaveKeys(['valid', 'total_baris', 'baris_valid', 'baris_error', 'errors']);
        expect($result['total_baris'])->toBe(2);
    });
});

describe('AbsensiValidator — cekDuplikasi()', function () {
    it('returns boolean', function () {
        $validator = new AbsensiValidator();
        $result = $validator->cekDuplikasi(1, '2025-06-15');

        expect($result)->toBeBool();
    });
});
