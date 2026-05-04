<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\KonfigurasiGaji;
use App\Models\PtKlien;
use Illuminate\Database\Seeder;

/**
 * Seeder untuk membuat contoh PT Klien beserta konfigurasi gaji.
 */
class PtKlienSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // PT ABC
        $ptAbc = PtKlien::updateOrCreate(
            ['nama' => 'PT ABC'],
            [
                'alamat' => 'Jl. Sudirman No. 100, Jakarta Selatan',
                'telepon' => '021-5551234',
                'email' => 'hrd@ptabc.co.id',
                'nama_pic' => 'Budi Santoso',
                'nomor_kontrak' => 'KTR-ABC-2025-001',
                'tgl_mulai' => '2025-01-01',
                'tgl_berakhir' => '2025-12-31',
                'fee_jasa' => 5000000.00,
            ]
        );

        KonfigurasiGaji::updateOrCreate(
            ['pt_klien_id' => $ptAbc->id],
            [
                'gaji_pokok_default' => 4000000.00,
                'jam_kerja_normal' => 8.00,
                'tarif_lembur_per_jam' => 25000.00,
                'potongan_per_hari' => 150000.00,
                'komponen_tunjangan' => [
                    ['nama' => 'Transport', 'nilai' => 500000],
                    ['nama' => 'Makan', 'nilai' => 300000],
                ],
            ]
        );

        // PT XYZ
        $ptXyz = PtKlien::updateOrCreate(
            ['nama' => 'PT XYZ'],
            [
                'alamat' => 'Jl. Gatot Subroto No. 55, Jakarta Pusat',
                'telepon' => '021-5559876',
                'email' => 'hrd@ptxyz.co.id',
                'nama_pic' => 'Siti Rahayu',
                'nomor_kontrak' => 'KTR-XYZ-2025-001',
                'tgl_mulai' => '2025-01-01',
                'tgl_berakhir' => '2025-12-31',
                'fee_jasa' => 7500000.00,
            ]
        );

        KonfigurasiGaji::updateOrCreate(
            ['pt_klien_id' => $ptXyz->id],
            [
                'gaji_pokok_default' => 4500000.00,
                'jam_kerja_normal' => 8.00,
                'tarif_lembur_per_jam' => 30000.00,
                'potongan_per_hari' => 175000.00,
                'komponen_tunjangan' => [
                    ['nama' => 'Transport', 'nilai' => 600000],
                    ['nama' => 'Makan', 'nilai' => 350000],
                    ['nama' => 'Jabatan', 'nilai' => 200000],
                ],
            ]
        );
    }
}
