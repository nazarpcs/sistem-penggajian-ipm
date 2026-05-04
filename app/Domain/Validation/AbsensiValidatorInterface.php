<?php

namespace App\Domain\Validation;

/**
 * Interface AbsensiValidator
 *
 * Memvalidasi data absensi dari input manual maupun file Excel.
 *
 * @see design.md — Komponen Inti: AbsensiValidator
 * @see Property 11: Validasi Import Excel — Atomicity
 * @see Property 12: Uniqueness Absensi per Karyawan per Tanggal
 * @see Property 20: Validasi Sinkron Sebelum Import Async
 */
interface AbsensiValidatorInterface
{
    /**
     * Validasi satu baris data absensi.
     *
     * @param array<string, mixed> $baris Data absensi satu baris
     * @return array{valid: bool, errors: array<string, string>}
     */
    public function validasiSatuBaris(array $baris): array;

    /**
     * Validasi seluruh baris data absensi (bulk/Excel).
     * Jika ada satu baris invalid, seluruh data ditolak (atomicity).
     *
     * @param array<int, array<string, mixed>> $rows Seluruh baris data
     * @return array{valid: bool, total_baris: int, baris_valid: int, baris_error: int, errors: array}
     */
    public function validasiBulk(array $rows): array;

    /**
     * Cek apakah sudah ada data absensi untuk karyawan pada tanggal tertentu.
     *
     * @param int $karyawanId ID karyawan
     * @param string $tanggal Tanggal absensi (Y-m-d)
     * @return bool True jika duplikat ditemukan
     */
    public function cekDuplikasi(int $karyawanId, string $tanggal): bool;
}
