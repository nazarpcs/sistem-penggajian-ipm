<?php

namespace App\Domain\Payroll;

/**
 * Implementasi KalkulatorGaji — pure domain class.
 *
 * Tidak memiliki dependensi framework Laravel apapun.
 * Menghitung gaji berdasarkan komponen input yang diberikan.
 *
 * Rumus:
 *   Gaji_Bersih = Gaji_Pokok + Total_Tunjangan + Total_Lembur - Total_Potongan
 *
 * @see KalkulatorGajiInterface
 * @see Property 13: Kebenaran Rumus Perhitungan Gaji
 * @see Property 18: Batas Minimum Gaji Bersih
 */
class KalkulatorGaji implements KalkulatorGajiInterface
{
    /**
     * Hitung gaji karyawan berdasarkan komponen input.
     *
     * Langkah perhitungan:
     * 1. totalTunjangan = Σ(nilai setiap komponen tunjangan)
     * 2. totalLembur = jamLembur × tarifLemburPerJam
     * 3. totalPotongan = hariAlpha × potonganPerHari
     * 4. gajiBersih = gajiPokok + totalTunjangan + totalLembur - totalPotongan
     * 5. Jika gajiBersih < 0 → set 0 dan tandai peringatan (Property 18)
     *
     * @param KomponenGaji $komponen Data komponen gaji untuk perhitungan
     * @return HasilPerhitunganGaji Hasil perhitungan gaji lengkap
     */
    public function hitung(KomponenGaji $komponen): HasilPerhitunganGaji
    {
        // 1. Hitung total tunjangan — Σ(nilai setiap komponen)
        $rincianTunjangan = $komponen->tunjangan;
        $totalTunjangan = 0;
        foreach ($rincianTunjangan as $nilai) {
            $totalTunjangan += $nilai;
        }

        // 2. Hitung total lembur
        $totalLembur = (int) round($komponen->jamLembur * $komponen->tarifLemburPerJam);

        // 3. Hitung total potongan
        $totalPotongan = $komponen->hariAlpha * $komponen->potonganPerHari;

        // 4. Hitung gaji bersih (Property 13)
        $gajiBersih = $komponen->gajiPokok + $totalTunjangan + $totalLembur - $totalPotongan;

        // 5. Enforce minimum 0 (Property 18)
        $peringatanNegatif = false;
        if ($gajiBersih < 0) {
            $gajiBersih = 0;
            $peringatanNegatif = true;
        }

        return new HasilPerhitunganGaji(
            gajiPokok: $komponen->gajiPokok,
            totalTunjangan: $totalTunjangan,
            totalLembur: $totalLembur,
            jamLembur: $komponen->jamLembur,
            totalPotongan: $totalPotongan,
            gajiBersih: $gajiBersih,
            rincianTunjangan: $rincianTunjangan,
            peringatanNegatif: $peringatanNegatif,
        );
    }
}
