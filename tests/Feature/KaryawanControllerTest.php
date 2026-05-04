<?php

// Feature: employee-payroll-system, Task 4.1: KaryawanController CRUD
// @see Req 3.1, 3.5, 3.6

use App\Models\Karyawan;
use App\Models\PtKlien;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
    ]);

    $this->ptKlien = PtKlien::create([
        'nama' => 'PT Test',
        'alamat' => 'Jl. Test',
        'telepon' => '021-1234567',
        'email' => 'test@pt.com',
        'nama_pic' => 'PIC',
        'nomor_kontrak' => 'KTR-001',
        'tgl_mulai' => '2025-01-01',
        'tgl_berakhir' => '2026-01-01',
        'fee_jasa' => 5000000,
    ]);
});

// ============================================================
// KaryawanRequest validation
// ============================================================

describe('KaryawanRequest validation', function () {
    it('should reject store with missing required fields', function () {
        $response = $this->actingAs($this->admin)
            ->post('/admin/karyawan', []);

        $response->assertSessionHasErrors([
            'nama_lengkap', 'nik', 'email', 'tanggal_lahir',
            'alamat', 'telepon', 'jabatan', 'gaji_pokok',
            'pt_klien_id', 'tanggal_bergabung',
        ]);
    });

    it('should reject nik that is not 16 digits', function () {
        $response = $this->actingAs($this->admin)
            ->post('/admin/karyawan', [
                'nama_lengkap' => 'Test',
                'nik' => '12345', // too short
                'email' => 'test@test.com',
                'tanggal_lahir' => '1990-01-01',
                'alamat' => 'Jl. Test',
                'telepon' => '08123456789',
                'jabatan' => 'Staff',
                'gaji_pokok' => 5000000,
                'pt_klien_id' => $this->ptKlien->id,
                'tanggal_bergabung' => '2025-01-01',
            ]);

        $response->assertSessionHasErrors('nik');
    });

    it('should reject non-numeric nik', function () {
        $response = $this->actingAs($this->admin)
            ->post('/admin/karyawan', [
                'nama_lengkap' => 'Test',
                'nik' => 'abcdefghijklmnop', // 16 chars but not digits
                'email' => 'test@test.com',
                'tanggal_lahir' => '1990-01-01',
                'alamat' => 'Jl. Test',
                'telepon' => '08123456789',
                'jabatan' => 'Staff',
                'gaji_pokok' => 5000000,
                'pt_klien_id' => $this->ptKlien->id,
                'tanggal_bergabung' => '2025-01-01',
            ]);

        $response->assertSessionHasErrors('nik');
    });

    it('should reject duplicate nik', function () {
        $existingUser = User::factory()->create(['role' => 'karyawan', 'is_active' => true]);
        Karyawan::create([
            'user_id' => $existingUser->id,
            'pt_klien_id' => $this->ptKlien->id,
            'nik' => '1234567890123456',
            'nama_lengkap' => 'Existing',
            'tanggal_lahir' => '1990-01-01',
            'alamat' => 'Jl. Test',
            'telepon' => '08111111111',
            'jabatan' => 'Staff',
            'gaji_pokok' => 5000000,
            'tanggal_bergabung' => '2025-01-01',
            'status_aktif' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->post('/admin/karyawan', [
                'nama_lengkap' => 'New Person',
                'nik' => '1234567890123456', // duplicate
                'email' => 'new@test.com',
                'tanggal_lahir' => '1990-01-01',
                'alamat' => 'Jl. Test',
                'telepon' => '08222222222',
                'jabatan' => 'Staff',
                'gaji_pokok' => 5000000,
                'pt_klien_id' => $this->ptKlien->id,
                'tanggal_bergabung' => '2025-01-01',
            ]);

        $response->assertSessionHasErrors('nik');
    });

    it('should reject duplicate email', function () {
        User::factory()->create(['email' => 'taken@test.com', 'role' => 'karyawan']);

        $response = $this->actingAs($this->admin)
            ->post('/admin/karyawan', [
                'nama_lengkap' => 'New Person',
                'nik' => '9999999999999999',
                'email' => 'taken@test.com', // duplicate
                'tanggal_lahir' => '1990-01-01',
                'alamat' => 'Jl. Test',
                'telepon' => '08222222222',
                'jabatan' => 'Staff',
                'gaji_pokok' => 5000000,
                'pt_klien_id' => $this->ptKlien->id,
                'tanggal_bergabung' => '2025-01-01',
            ]);

        $response->assertSessionHasErrors('email');
    });

    it('should reject negative gaji_pokok', function () {
        $response = $this->actingAs($this->admin)
            ->post('/admin/karyawan', [
                'nama_lengkap' => 'Test',
                'nik' => '1234567890123456',
                'email' => 'test@test.com',
                'tanggal_lahir' => '1990-01-01',
                'alamat' => 'Jl. Test',
                'telepon' => '08123456789',
                'jabatan' => 'Staff',
                'gaji_pokok' => -100000,
                'pt_klien_id' => $this->ptKlien->id,
                'tanggal_bergabung' => '2025-01-01',
            ]);

        $response->assertSessionHasErrors('gaji_pokok');
    });

    it('should reject invalid pt_klien_id', function () {
        $response = $this->actingAs($this->admin)
            ->post('/admin/karyawan', [
                'nama_lengkap' => 'Test',
                'nik' => '1234567890123456',
                'email' => 'test@test.com',
                'tanggal_lahir' => '1990-01-01',
                'alamat' => 'Jl. Test',
                'telepon' => '08123456789',
                'jabatan' => 'Staff',
                'gaji_pokok' => 5000000,
                'pt_klien_id' => 99999,
                'tanggal_bergabung' => '2025-01-01',
            ]);

        $response->assertSessionHasErrors('pt_klien_id');
    });
});
