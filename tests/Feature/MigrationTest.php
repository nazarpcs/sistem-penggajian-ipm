<?php

// Feature: employee-payroll-system
// Feature test untuk Migrations — verifikasi semua tabel dan constraint terbuat dengan benar.
// Validates: Seluruh requirements terkait skema database

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

describe('Migration — Tabel Terbuat', function () {
    it('creates all required tables after migration', function () {
        $requiredTables = [
            'users',
            'password_reset_tokens',
            'sessions',
            'cache',
            'jobs',
            'job_batches',
            'failed_jobs',
            'pt_klien',
            'karyawan',
            'konfigurasi_gaji',
            'absensi',
            'periode_penggajian',
            'slip_gaji',
            'komponen_slip_gaji',
            'invoice',
            'audit_logs',
        ];

        foreach ($requiredTables as $table) {
            expect(Schema::hasTable($table))->toBeTrue("Table '{$table}' should exist");
        }
    });
});

describe('Migration — Tabel Users', function () {
    it('has role column', function () {
        expect(Schema::hasColumn('users', 'role'))->toBeTrue();
    });

    it('has is_active column', function () {
        expect(Schema::hasColumn('users', 'is_active'))->toBeTrue();
    });

    it('has locked_until column', function () {
        expect(Schema::hasColumn('users', 'locked_until'))->toBeTrue();
    });

    it('has login_attempts column', function () {
        expect(Schema::hasColumn('users', 'login_attempts'))->toBeTrue();
    });

    it('has last_login column', function () {
        expect(Schema::hasColumn('users', 'last_login'))->toBeTrue();
    });
});

describe('Migration — Unique Constraints', function () {
    // Feature: employee-payroll-system, Property 12: Uniqueness Absensi per Karyawan per Tanggal
    it('enforces unique constraint on absensi karyawan_id + tanggal', function () {
        $ptKlien = \App\Models\PtKlien::create([
            'nama' => 'PT Unique Test',
            'alamat' => 'Jl. Test',
            'telepon' => '021-1234567',
            'email' => 'unique@pt.co.id',
            'nama_pic' => 'PIC',
            'nomor_kontrak' => 'KTR-UNQ-001',
            'tgl_mulai' => '2025-01-01',
            'tgl_berakhir' => '2025-12-31',
            'fee_jasa' => 5000000,
        ]);

        $user = \App\Models\User::factory()->create(['role' => 'karyawan']);

        $karyawan = \App\Models\Karyawan::create([
            'user_id' => $user->id,
            'pt_klien_id' => $ptKlien->id,
            'nik' => 'KRY-UNQ-001',
            'nama_lengkap' => 'Test Unique',
            'tanggal_lahir' => '1990-01-01',
            'alamat' => 'Jl. Test',
            'telepon' => '081234567890',
            'jabatan' => 'Staff',
            'gaji_pokok' => 4000000,
            'tanggal_bergabung' => '2025-01-01',
            'status_aktif' => true,
        ]);

        \App\Models\Absensi::create([
            'karyawan_id' => $karyawan->id,
            'tanggal' => '2025-06-15',
            'status_kehadiran' => 'Hadir',
        ]);

        // Duplikasi harus gagal
        expect(fn () => \App\Models\Absensi::create([
            'karyawan_id' => $karyawan->id,
            'tanggal' => '2025-06-15',
            'status_kehadiran' => 'Izin',
        ]))->toThrow(\Illuminate\Database\QueryException::class);
    });

    // Feature: employee-payroll-system, Property 16: Pencegahan Duplikasi Invoice
    it('enforces unique constraint on invoice pt_klien_id + periode_id', function () {
        $ptKlien = \App\Models\PtKlien::create([
            'nama' => 'PT Invoice Unique',
            'alamat' => 'Jl. Test',
            'telepon' => '021-1234567',
            'email' => 'inv-unique@pt.co.id',
            'nama_pic' => 'PIC',
            'nomor_kontrak' => 'KTR-INV-UNQ-001',
            'tgl_mulai' => '2025-01-01',
            'tgl_berakhir' => '2025-12-31',
            'fee_jasa' => 5000000,
        ]);

        $periode = \App\Models\PeriodePenggajian::create([
            'bulan' => 6,
            'tahun' => 2025,
            'tanggal_mulai' => '2025-06-01',
            'tanggal_selesai' => '2025-06-30',
            'status' => 'aktif',
        ]);

        \App\Models\Invoice::create([
            'pt_klien_id' => $ptKlien->id,
            'periode_id' => $periode->id,
            'nomor_invoice' => 'IPM-INV-2025-06-001',
            'tanggal_pembuatan' => '2025-06-30',
            'subtotal_gaji' => 20000000,
            'fee_jasa' => 5000000,
            'pajak' => 0,
            'total_tagihan' => 25000000,
        ]);

        // Duplikasi pt_klien_id + periode_id harus gagal
        expect(fn () => \App\Models\Invoice::create([
            'pt_klien_id' => $ptKlien->id,
            'periode_id' => $periode->id,
            'nomor_invoice' => 'IPM-INV-2025-06-002',
            'tanggal_pembuatan' => '2025-06-30',
            'subtotal_gaji' => 20000000,
            'fee_jasa' => 5000000,
            'pajak' => 0,
            'total_tagihan' => 25000000,
        ]))->toThrow(\Illuminate\Database\QueryException::class);
    });

    // Feature: employee-payroll-system, Property 15: Format dan Uniqueness Nomor Invoice
    it('enforces unique constraint on invoice nomor_invoice', function () {
        $ptKlien = \App\Models\PtKlien::create([
            'nama' => 'PT Nomor Unique',
            'alamat' => 'Jl. Test',
            'telepon' => '021-1234567',
            'email' => 'nomor-unique@pt.co.id',
            'nama_pic' => 'PIC',
            'nomor_kontrak' => 'KTR-NOM-UNQ-001',
            'tgl_mulai' => '2025-01-01',
            'tgl_berakhir' => '2025-12-31',
            'fee_jasa' => 5000000,
        ]);

        $periode1 = \App\Models\PeriodePenggajian::create([
            'bulan' => 5,
            'tahun' => 2025,
            'tanggal_mulai' => '2025-05-01',
            'tanggal_selesai' => '2025-05-31',
            'status' => 'aktif',
        ]);

        $periode2 = \App\Models\PeriodePenggajian::create([
            'bulan' => 6,
            'tahun' => 2025,
            'tanggal_mulai' => '2025-06-01',
            'tanggal_selesai' => '2025-06-30',
            'status' => 'aktif',
        ]);

        \App\Models\Invoice::create([
            'pt_klien_id' => $ptKlien->id,
            'periode_id' => $periode1->id,
            'nomor_invoice' => 'IPM-NOM-2025-05-001',
            'tanggal_pembuatan' => '2025-05-31',
            'subtotal_gaji' => 20000000,
            'fee_jasa' => 5000000,
            'pajak' => 0,
            'total_tagihan' => 25000000,
        ]);

        // Nomor invoice yang sama harus gagal
        expect(fn () => \App\Models\Invoice::create([
            'pt_klien_id' => $ptKlien->id,
            'periode_id' => $periode2->id,
            'nomor_invoice' => 'IPM-NOM-2025-05-001',
            'tanggal_pembuatan' => '2025-06-30',
            'subtotal_gaji' => 20000000,
            'fee_jasa' => 5000000,
            'pajak' => 0,
            'total_tagihan' => 25000000,
        ]))->toThrow(\Illuminate\Database\QueryException::class);
    });

    it('enforces unique constraint on periode_penggajian bulan + tahun', function () {
        \App\Models\PeriodePenggajian::create([
            'bulan' => 1,
            'tahun' => 2025,
            'tanggal_mulai' => '2025-01-01',
            'tanggal_selesai' => '2025-01-31',
            'status' => 'aktif',
        ]);

        // Duplikasi bulan + tahun harus gagal
        expect(fn () => \App\Models\PeriodePenggajian::create([
            'bulan' => 1,
            'tahun' => 2025,
            'tanggal_mulai' => '2025-01-01',
            'tanggal_selesai' => '2025-01-31',
            'status' => 'aktif',
        ]))->toThrow(\Illuminate\Database\QueryException::class);
    });
});
