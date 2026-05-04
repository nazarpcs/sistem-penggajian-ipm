<?php

namespace App\Domain\Payroll;

/**
 * Interface KalkulatorGaji
 *
 * Komponen domain murni (pure class, tanpa dependensi framework)
 * yang menghitung gaji berdasarkan konfigurasi PT Klien.
 *
 * Rumus utama:
 *   Gaji Bersih = Gaji_Pokok + Total_Tunjangan + Total_Lembur - Total_Potongan
 *   Total_Lembur = jam_lembur × tarif_lembur_per_jam
 *   Total_Potongan = hari_alpha × potongan_per_hari
 *   Gaji Bersih minimum = 0 (tidak pernah negatif)
 *
 * @see design.md — Komponen Inti: KalkulatorGaji
 * @see Property 13: Kebenaran Rumus Perhitungan Gaji
 * @see Property 18: Batas Minimum Gaji Bersih
 */
interface KalkulatorGajiInterface
{
    /**
     * Hitung gaji karyawan berdasarkan data absensi dan konfigurasi.
     *
     * @param KomponenGaji $komponen Data komponen gaji untuk perhitungan
     * @return HasilPerhitunganGaji Hasil perhitungan gaji lengkap
     */
    public function hitung(KomponenGaji $komponen): HasilPerhitunganGaji;
}
