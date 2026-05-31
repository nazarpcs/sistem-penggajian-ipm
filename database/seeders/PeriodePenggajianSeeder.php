<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PeriodePenggajian;
use Illuminate\Database\Seeder;

/**
 * Seeder untuk PeriodePenggajian — buat periode 6 bulan terakhir.
 */
class PeriodePenggajianSeeder extends Seeder
{
    public function run(): void
    {
        $periodes = [
            ['bulan' => 1, 'tahun' => 2025, 'tanggal_mulai' => '2025-01-01', 'tanggal_selesai' => '2025-01-31'],
            ['bulan' => 2, 'tahun' => 2025, 'tanggal_mulai' => '2025-02-01', 'tanggal_selesai' => '2025-02-28'],
            ['bulan' => 3, 'tahun' => 2025, 'tanggal_mulai' => '2025-03-01', 'tanggal_selesai' => '2025-03-31'],
            ['bulan' => 4, 'tahun' => 2025, 'tanggal_mulai' => '2025-04-01', 'tanggal_selesai' => '2025-04-30'],
            ['bulan' => 5, 'tahun' => 2025, 'tanggal_mulai' => '2025-05-01', 'tanggal_selesai' => '2025-05-31'],
            ['bulan' => 6, 'tahun' => 2025, 'tanggal_mulai' => '2025-06-01', 'tanggal_selesai' => '2025-06-30'],
        ];

        foreach ($periodes as $periode) {
            PeriodePenggajian::firstOrCreate(
                ['bulan' => $periode['bulan'], 'tahun' => $periode['tahun']],
                $periode,
            );
        }
    }
}
