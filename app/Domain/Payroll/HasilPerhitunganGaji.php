<?php

namespace App\Domain\Payroll;

/**
 * DTO (Data Transfer Object) untuk hasil perhitungan gaji.
 *
 * Immutable value object — semua properti readonly.
 * Dihasilkan oleh KalkulatorGaji::hitung().
 *
 * @see design.md — Komponen Inti: KalkulatorGaji
 * @see Property 13: Kebenaran Rumus Perhitungan Gaji
 * @see Property 18: Batas Minimum Gaji Bersih
 */
final readonly class HasilPerhitunganGaji
{
    /**
     * @param int $gajiPokok Gaji pokok karyawan
     * @param int $totalTunjangan Total seluruh komponen tunjangan
     * @param int $totalLembur Total lembur (jam_lembur × tarif_lembur_per_jam)
     * @param float $jamLembur Jumlah jam lembur
     * @param int $totalPotongan Total potongan (hari_alpha × potongan_per_hari)
     * @param int $gajiBersih Gaji bersih (minimum 0, tidak pernah negatif)
     * @param array<string, int> $rincianTunjangan Rincian per komponen tunjangan
     * @param bool $peringatanNegatif True jika perhitungan awal menghasilkan nilai negatif
     */
    public function __construct(
        public int $gajiPokok,
        public int $totalTunjangan,
        public int $totalLembur,
        public float $jamLembur,
        public int $totalPotongan,
        public int $gajiBersih,
        public array $rincianTunjangan = [],
        public bool $peringatanNegatif = false,
    ) {}
}
