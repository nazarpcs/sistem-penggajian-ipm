<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Karyawan;
use App\Models\PtKlien;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder untuk membuat contoh karyawan beserta akun user otomatis.
 *
 * 3 karyawan di PT ABC, 2 karyawan di PT XYZ.
 */
class KaryawanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ptAbc = PtKlien::where('nama', 'PT ABC')->firstOrFail();
        $ptXyz = PtKlien::where('nama', 'PT XYZ')->firstOrFail();

        $karyawanData = [
            // 3 karyawan PT ABC
            [
                'pt_klien' => $ptAbc,
                'nik' => 'KRY-001',
                'nama_lengkap' => 'Andi Pratama',
                'email' => 'andi.pratama@ipm.test',
                'tanggal_lahir' => '1990-05-15',
                'alamat' => 'Jl. Mangga Dua No. 10, Jakarta Utara',
                'telepon' => '081234567001',
                'jabatan' => 'Staff Administrasi',
                'gaji_pokok' => 4000000.00,
                'tanggal_bergabung' => '2025-01-15',
            ],
            [
                'pt_klien' => $ptAbc,
                'nik' => 'KRY-002',
                'nama_lengkap' => 'Dewi Lestari',
                'email' => 'dewi.lestari@ipm.test',
                'tanggal_lahir' => '1992-08-22',
                'alamat' => 'Jl. Kebon Jeruk No. 5, Jakarta Barat',
                'telepon' => '081234567002',
                'jabatan' => 'Staff Keuangan',
                'gaji_pokok' => 4200000.00,
                'tanggal_bergabung' => '2025-02-01',
            ],
            [
                'pt_klien' => $ptAbc,
                'nik' => 'KRY-003',
                'nama_lengkap' => 'Rizky Hidayat',
                'email' => 'rizky.hidayat@ipm.test',
                'tanggal_lahir' => '1988-12-03',
                'alamat' => 'Jl. Cempaka Putih No. 20, Jakarta Pusat',
                'telepon' => '081234567003',
                'jabatan' => 'Staff IT',
                'gaji_pokok' => 4500000.00,
                'tanggal_bergabung' => '2025-01-20',
            ],
            // 2 karyawan PT XYZ
            [
                'pt_klien' => $ptXyz,
                'nik' => 'KRY-004',
                'nama_lengkap' => 'Sari Wulandari',
                'email' => 'sari.wulandari@ipm.test',
                'tanggal_lahir' => '1995-03-10',
                'alamat' => 'Jl. Kemang Raya No. 8, Jakarta Selatan',
                'telepon' => '081234567004',
                'jabatan' => 'Staff Marketing',
                'gaji_pokok' => 4500000.00,
                'tanggal_bergabung' => '2025-01-10',
            ],
            [
                'pt_klien' => $ptXyz,
                'nik' => 'KRY-005',
                'nama_lengkap' => 'Fajar Nugroho',
                'email' => 'fajar.nugroho@ipm.test',
                'tanggal_lahir' => '1993-07-28',
                'alamat' => 'Jl. Tebet Raya No. 15, Jakarta Selatan',
                'telepon' => '081234567005',
                'jabatan' => 'Staff Operasional',
                'gaji_pokok' => 4300000.00,
                'tanggal_bergabung' => '2025-02-15',
            ],
        ];

        foreach ($karyawanData as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['nama_lengkap'],
                    'password' => bcrypt('password'),
                    'role' => 'karyawan',
                    'is_active' => true,
                ]
            );

            Karyawan::updateOrCreate(
                ['nik' => $data['nik']],
                [
                    'user_id' => $user->id,
                    'pt_klien_id' => $data['pt_klien']->id,
                    'nama_lengkap' => $data['nama_lengkap'],
                    'tanggal_lahir' => $data['tanggal_lahir'],
                    'alamat' => $data['alamat'],
                    'telepon' => $data['telepon'],
                    'jabatan' => $data['jabatan'],
                    'gaji_pokok' => $data['gaji_pokok'],
                    'tanggal_bergabung' => $data['tanggal_bergabung'],
                    'status_aktif' => true,
                ]
            );
        }
    }
}
