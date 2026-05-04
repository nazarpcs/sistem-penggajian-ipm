<?php

// Feature: employee-payroll-system, Task 4.2: KaryawanObserver
// @see Req 3.2, 3.3, 3.7
// @see Property 8: Pembuatan Akun Otomatis Saat Karyawan Baru Dibuat
// @see Property 9: Sinkronisasi Status Karyawan dan Akun Login

use App\Models\Karyawan;
use App\Models\PtKlien;
use App\Models\User;
use App\Notifications\KredensialKaryawanNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
    ]);
    $this->actingAs($this->admin);

    $this->ptKlien = PtKlien::create([
        'nama' => 'PT Observer Test',
        'alamat' => 'Jl. Test No. 1',
        'telepon' => '021-1234567',
        'email' => 'observer@ptklien.com',
        'nama_pic' => 'PIC Test',
        'nomor_kontrak' => 'KTR-OBS-001',
        'tgl_mulai' => '2025-01-01',
        'tgl_berakhir' => '2026-01-01',
        'fee_jasa' => 5000000,
    ]);
});

// ============================================================
// creating() — Pembuatan akun User otomatis
// ============================================================

describe('creating — auto user creation', function () {
    it('should create a User automatically when karyawan is created without user_id', function () {
        Notification::fake();

        $karyawan = new Karyawan([
            'pt_klien_id' => $this->ptKlien->id,
            'nik' => '1234567890123456',
            'nama_lengkap' => 'Budi Santoso',
            'tanggal_lahir' => '1990-05-15',
            'alamat' => 'Jl. Merdeka No. 10',
            'telepon' => '08123456789',
            'jabatan' => 'Staff IT',
            'gaji_pokok' => 5000000,
            'tanggal_bergabung' => '2025-01-01',
            'status_aktif' => true,
        ]);
        $karyawan->setTemporaryEmail('budi@example.com');
        $karyawan->save();

        expect($karyawan->user_id)->not->toBeNull();

        $user = User::find($karyawan->user_id);
        expect($user)->not->toBeNull()
            ->and($user->name)->toBe('Budi Santoso')
            ->and($user->email)->toBe('budi@example.com')
            ->and($user->role)->toBe('karyawan')
            ->and($user->is_active)->toBeTrue();
    });

    it('should generate a hashed password for the auto-created user', function () {
        Notification::fake();

        $karyawan = new Karyawan([
            'pt_klien_id' => $this->ptKlien->id,
            'nik' => '2222222222222222',
            'nama_lengkap' => 'Ani Wijaya',
            'tanggal_lahir' => '1992-03-20',
            'alamat' => 'Jl. Sudirman No. 5',
            'telepon' => '08198765432',
            'jabatan' => 'Staff HR',
            'gaji_pokok' => 4500000,
            'tanggal_bergabung' => '2025-02-01',
            'status_aktif' => true,
        ]);
        $karyawan->setTemporaryEmail('ani@example.com');
        $karyawan->save();

        $user = User::find($karyawan->user_id);

        // Password should be hashed (not plaintext)
        expect($user->password)->not->toBe('')
            ->and(Hash::isHashed($user->password))->toBeTrue();
    });

    it('should not create a User when user_id is already set', function () {
        $existingUser = User::factory()->create([
            'role' => 'karyawan',
            'is_active' => true,
        ]);

        $userCountBefore = User::count();

        $karyawan = Karyawan::create([
            'user_id' => $existingUser->id,
            'pt_klien_id' => $this->ptKlien->id,
            'nik' => '3333333333333333',
            'nama_lengkap' => 'Citra Dewi',
            'tanggal_lahir' => '1995-07-10',
            'alamat' => 'Jl. Gatot Subroto',
            'telepon' => '08111222333',
            'jabatan' => 'Staff Keuangan',
            'gaji_pokok' => 5500000,
            'tanggal_bergabung' => '2025-03-01',
            'status_aktif' => true,
        ]);

        expect(User::count())->toBe($userCountBefore)
            ->and($karyawan->user_id)->toBe($existingUser->id);
    });
});

// ============================================================
// created() — Kirim notifikasi kredensial
// ============================================================

describe('created — send credential notification', function () {
    it('should send KredensialKaryawanNotification when user is auto-created', function () {
        Notification::fake();

        $karyawan = new Karyawan([
            'pt_klien_id' => $this->ptKlien->id,
            'nik' => '4444444444444444',
            'nama_lengkap' => 'Dedi Kurniawan',
            'tanggal_lahir' => '1988-11-25',
            'alamat' => 'Jl. Thamrin No. 3',
            'telepon' => '08155566677',
            'jabatan' => 'Supervisor',
            'gaji_pokok' => 7000000,
            'tanggal_bergabung' => '2025-04-01',
            'status_aktif' => true,
        ]);
        $karyawan->setTemporaryEmail('dedi@example.com');
        $karyawan->save();

        $user = User::find($karyawan->user_id);

        Notification::assertSentTo($user, KredensialKaryawanNotification::class);
    });

    it('should not send notification when user_id was already set', function () {
        Notification::fake();

        $existingUser = User::factory()->create([
            'role' => 'karyawan',
            'is_active' => true,
        ]);

        Karyawan::create([
            'user_id' => $existingUser->id,
            'pt_klien_id' => $this->ptKlien->id,
            'nik' => '5555555555555555',
            'nama_lengkap' => 'Eka Putri',
            'tanggal_lahir' => '1993-09-12',
            'alamat' => 'Jl. Kuningan',
            'telepon' => '08177788899',
            'jabatan' => 'Staff Admin',
            'gaji_pokok' => 4000000,
            'tanggal_bergabung' => '2025-05-01',
            'status_aktif' => true,
        ]);

        Notification::assertNotSentTo($existingUser, KredensialKaryawanNotification::class);
    });
});

// ============================================================
// updated() — Sinkronisasi status aktif
// ============================================================

describe('updated — status synchronization', function () {
    it('should deactivate user when karyawan status_aktif becomes false', function () {
        Notification::fake();

        $karyawan = new Karyawan([
            'pt_klien_id' => $this->ptKlien->id,
            'nik' => '6666666666666666',
            'nama_lengkap' => 'Fajar Hidayat',
            'tanggal_lahir' => '1991-01-15',
            'alamat' => 'Jl. Rasuna Said',
            'telepon' => '08199900011',
            'jabatan' => 'Staff Operasional',
            'gaji_pokok' => 4800000,
            'tanggal_bergabung' => '2025-01-01',
            'status_aktif' => true,
        ]);
        $karyawan->setTemporaryEmail('fajar@example.com');
        $karyawan->save();

        $user = User::find($karyawan->user_id);
        expect($user->is_active)->toBeTrue();

        // Nonaktifkan karyawan
        $karyawan->update(['status_aktif' => false]);

        $user->refresh();
        expect($user->is_active)->toBeFalse();
    });

    it('should reactivate user when karyawan status_aktif becomes true', function () {
        Notification::fake();

        $karyawan = new Karyawan([
            'pt_klien_id' => $this->ptKlien->id,
            'nik' => '7777777777777777',
            'nama_lengkap' => 'Gita Permata',
            'tanggal_lahir' => '1994-06-20',
            'alamat' => 'Jl. Casablanca',
            'telepon' => '08122233344',
            'jabatan' => 'Staff Marketing',
            'gaji_pokok' => 5200000,
            'tanggal_bergabung' => '2025-01-01',
            'status_aktif' => false,
        ]);
        $karyawan->setTemporaryEmail('gita@example.com');
        $karyawan->save();

        // Aktifkan karyawan
        $karyawan->update(['status_aktif' => true]);

        $user = User::find($karyawan->user_id);
        expect($user->is_active)->toBeTrue();
    });

    it('should not change user when status_aktif is not changed', function () {
        Notification::fake();

        $karyawan = new Karyawan([
            'pt_klien_id' => $this->ptKlien->id,
            'nik' => '8888888888888888',
            'nama_lengkap' => 'Hadi Pranoto',
            'tanggal_lahir' => '1989-12-05',
            'alamat' => 'Jl. Senayan',
            'telepon' => '08133344455',
            'jabatan' => 'Staff Gudang',
            'gaji_pokok' => 4200000,
            'tanggal_bergabung' => '2025-01-01',
            'status_aktif' => true,
        ]);
        $karyawan->setTemporaryEmail('hadi@example.com');
        $karyawan->save();

        $user = User::find($karyawan->user_id);
        $updatedAt = $user->updated_at;

        // Update field lain, bukan status_aktif
        $karyawan->update(['jabatan' => 'Kepala Gudang']);

        $user->refresh();
        expect($user->is_active)->toBeTrue();
    });
});
