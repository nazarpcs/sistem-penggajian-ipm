<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * DatabaseSeeder — entry point untuk seluruh seeder.
 *
 * Urutan eksekusi penting karena ada dependensi antar tabel:
 * 1. AdminSeeder (users)
 * 2. PtKlienSeeder (pt_klien + konfigurasi_gaji)
 * 3. KaryawanSeeder (users + karyawan, depends on pt_klien)
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            PtKlienSeeder::class,
            KaryawanSeeder::class,
        ]);
    }
}
