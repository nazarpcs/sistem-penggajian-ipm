<?php

namespace App\Domain\Payroll;

/**
 * DTO (Data Transfer Object) untuk komponen input perhitungan gaji.
 *
 * Immutable value object — semua properti readonly.
 * Digunakan sebagai input ke KalkulatorGaji::hitung().
 *
 * @see design.md — Komponen Inti: KalkulatorGaji
 */
final readonly class KomponenGaji
{
    /**
     * @param int $gajiPokok Gaji pokok karyawan (dalam Rupiah)
     * @param array<string, int> $tunjangan Daftar tunjangan [nama => nilai]
     * @param float $jamLembur Total jam lembur dalam periode
     * @param int $tarifLemburPerJam Tarif lembur per jam (dalam Rupiah)
     * @param int $hariAlpha Jumlah hari alpha (tidak hadir tanpa keterangan)
     * @param int $potonganPerHari Potongan per hari alpha (dalam Rupiah)
     */
    public function __construct(
        public int $gajiPokok,
        public array $tunjangan = [],
        public float $jamLembur = 0,
        public int $tarifLemburPerJam = 0,
        public int $hariAlpha = 0,
        public int $potonganPerHari = 0,
    ) {}
}
