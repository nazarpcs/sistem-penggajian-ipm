<?php

// Feature: employee-payroll-system
// Feature test untuk Seeders — verifikasi data awal terbuat dengan benar.
// Validates: Req 2.1 (Tiga peran), Req 3 (Data Karyawan), Req 4 (Data PT Klien)

use App\Models\Karyawan;
use App\Models\KonfigurasiGaji;
use App\Models\PtKlien;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\KaryawanSeeder;
use Database\Seeders\PtKlienSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('AdminSeeder', function () {
    it('creates admin user with email admin@ipm.test', function () {
        $this->seed(AdminSeeder::class);

        $admin = User::where('email', 'admin@ipm.test')->first();

        expect($admin)->not->toBeNull();
        expect($admin->name)->toBe('Administrator');
        expect($admin->role)->toBe('admin');
        expect($admin->is_active)->toBeTrue();
    });

    it('creates admin with hashed password', function () {
        $this->seed(AdminSeeder::class);

        $admin = User::where('email', 'admin@ipm.test')->first();

        // Password harus di-hash, bukan plaintext
        expect($admin->password)->not->toBe('password');
        expect(password_verify('password', $admin->password))->toBeTrue();
    });
});

describe('PtKlienSeeder', function () {
    it('creates PT ABC with konfigurasi gaji', function () {
        $this->seed(PtKlienSeeder::class);

        $ptAbc = PtKlien::where('nama', 'PT ABC')->first();

        expect($ptAbc)->not->toBeNull();
        expect($ptAbc->email)->toBe('hrd@ptabc.co.id');
        expect($ptAbc->nama_pic)->toBe('Budi Santoso');

        $config = KonfigurasiGaji::where('pt_klien_id', $ptAbc->id)->first();
        expect($config)->not->toBeNull();
        expect($config->komponen_tunjangan)->toBeArray();
        expect($config->komponen_tunjangan)->toHaveCount(2);
    });

    it('creates PT XYZ with konfigurasi gaji', function () {
        $this->seed(PtKlienSeeder::class);

        $ptXyz = PtKlien::where('nama', 'PT XYZ')->first();

        expect($ptXyz)->not->toBeNull();
        expect($ptXyz->email)->toBe('hrd@ptxyz.co.id');
        expect($ptXyz->nama_pic)->toBe('Siti Rahayu');

        $config = KonfigurasiGaji::where('pt_klien_id', $ptXyz->id)->first();
        expect($config)->not->toBeNull();
        expect($config->komponen_tunjangan)->toBeArray();
        expect($config->komponen_tunjangan)->toHaveCount(3);
    });

    it('creates exactly 2 PT Klien', function () {
        $this->seed(PtKlienSeeder::class);
        expect(PtKlien::count())->toBe(2);
    });
});

describe('KaryawanSeeder', function () {
    it('creates 5 karyawan with user accounts', function () {
        $this->seed(PtKlienSeeder::class);
        $this->seed(KaryawanSeeder::class);

        expect(Karyawan::count())->toBe(5);

        // Setiap karyawan harus punya akun user
        $karyawanUsers = User::where('role', 'karyawan')->count();
        expect($karyawanUsers)->toBe(5);
    });

    it('creates 3 karyawan for PT ABC and 2 for PT XYZ', function () {
        $this->seed(PtKlienSeeder::class);
        $this->seed(KaryawanSeeder::class);

        $ptAbc = PtKlien::where('nama', 'PT ABC')->first();
        $ptXyz = PtKlien::where('nama', 'PT XYZ')->first();

        expect(Karyawan::where('pt_klien_id', $ptAbc->id)->count())->toBe(3);
        expect(Karyawan::where('pt_klien_id', $ptXyz->id)->count())->toBe(2);
    });

    it('creates karyawan with active status', function () {
        $this->seed(PtKlienSeeder::class);
        $this->seed(KaryawanSeeder::class);

        $allActive = Karyawan::where('status_aktif', true)->count();
        expect($allActive)->toBe(5);
    });
});

describe('DatabaseSeeder', function () {
    it('runs all seeders without error', function () {
        $this->seed(DatabaseSeeder::class);

        // Verifikasi semua data terbuat
        expect(User::where('role', 'admin')->count())->toBe(1);
        expect(PtKlien::count())->toBe(2);
        expect(KonfigurasiGaji::count())->toBe(2);
        expect(Karyawan::count())->toBe(5);
        expect(User::where('role', 'karyawan')->count())->toBe(5);
    });

    it('is idempotent (can run twice without error)', function () {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        // updateOrCreate memastikan tidak ada duplikasi
        expect(User::where('email', 'admin@ipm.test')->count())->toBe(1);
        expect(PtKlien::where('nama', 'PT ABC')->count())->toBe(1);
    });
});
