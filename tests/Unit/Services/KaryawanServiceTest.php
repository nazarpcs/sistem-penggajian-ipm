<?php

// Feature: employee-payroll-system, Task 4.1: KaryawanService CRUD + filter
// @see Req 3.1, 3.5, 3.6
// @see Property 7: Penyimpanan Data Karyawan (Round-Trip)
// @see Property 10: Filter Karyawan Konsisten

use App\Models\AuditLog;
use App\Models\Karyawan;
use App\Models\PtKlien;
use App\Models\User;
use App\Services\KaryawanService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new KaryawanService();

    // Create admin user and authenticate for audit log
    $this->admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
    ]);
    $this->actingAs($this->admin);

    // Create a PT Klien for karyawan
    $this->ptKlien = PtKlien::create([
        'nama' => 'PT Test Klien',
        'alamat' => 'Jl. Test No. 1',
        'telepon' => '021-1234567',
        'email' => 'test@ptklien.com',
        'nama_pic' => 'PIC Test',
        'nomor_kontrak' => 'KTR-001',
        'tgl_mulai' => '2025-01-01',
        'tgl_berakhir' => '2026-01-01',
        'fee_jasa' => 5000000,
    ]);
});

/**
 * Helper: create a karyawan with a linked user.
 */
function createKaryawan(array $overrides = [], ?PtKlien $ptKlien = null): Karyawan
{
    $user = User::factory()->create([
        'role' => 'karyawan',
        'is_active' => true,
        'email' => $overrides['email'] ?? fake()->unique()->safeEmail(),
    ]);

    $ptKlien = $ptKlien ?? PtKlien::first();

    return Karyawan::create(array_merge([
        'user_id' => $user->id,
        'pt_klien_id' => $ptKlien->id,
        'nik' => fake()->numerify('################'),
        'nama_lengkap' => fake()->name(),
        'tanggal_lahir' => '1990-05-15',
        'alamat' => 'Jl. Test No. 1',
        'telepon' => '08123456789',
        'jabatan' => 'Staff',
        'gaji_pokok' => 5000000,
        'tanggal_bergabung' => '2025-01-01',
        'status_aktif' => true,
    ], $overrides));
}

// ============================================================
// store() — Simpan karyawan baru
// ============================================================

describe('store', function () {
    it('should create a new karyawan and return the model', function () {
        $user = User::factory()->create(['role' => 'karyawan', 'is_active' => true]);

        $data = [
            'user_id' => $user->id,
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
        ];

        $karyawan = $this->service->store($data);

        expect($karyawan)->toBeInstanceOf(Karyawan::class)
            ->and($karyawan->nama_lengkap)->toBe('Budi Santoso')
            ->and($karyawan->nik)->toBe('1234567890123456')
            ->and($karyawan->jabatan)->toBe('Staff IT');
    });

    it('should create an audit log entry on store', function () {
        $user = User::factory()->create(['role' => 'karyawan', 'is_active' => true]);

        $this->service->store([
            'user_id' => $user->id,
            'pt_klien_id' => $this->ptKlien->id,
            'nik' => '9876543210123456',
            'nama_lengkap' => 'Audit Test',
            'tanggal_lahir' => '1990-01-01',
            'alamat' => 'Jl. Audit',
            'telepon' => '08111111111',
            'jabatan' => 'Tester',
            'gaji_pokok' => 4000000,
            'tanggal_bergabung' => '2025-01-01',
            'status_aktif' => true,
        ]);

        $log = AuditLog::where('jenis_aktivitas', 'create_karyawan')->first();
        expect($log)->not->toBeNull()
            ->and($log->model_tipe)->toBe('Karyawan')
            ->and($log->user_id)->toBe($this->admin->id);
    });
});

// ============================================================
// show() — Ambil detail karyawan
// ============================================================

describe('show', function () {
    it('should return karyawan with ptKlien and user relations loaded', function () {
        $karyawan = createKaryawan([], $this->ptKlien);

        $result = $this->service->show($karyawan->id);

        expect($result->id)->toBe($karyawan->id)
            ->and($result->relationLoaded('ptKlien'))->toBeTrue()
            ->and($result->relationLoaded('user'))->toBeTrue();
    });

    it('should throw ModelNotFoundException for non-existent id', function () {
        $this->service->show(99999);
    })->throws(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
});

// ============================================================
// update() — Update data karyawan
// ============================================================

describe('update', function () {
    it('should update karyawan data and return updated model', function () {
        $karyawan = createKaryawan(['jabatan' => 'Staff'], $this->ptKlien);

        $result = $this->service->update($karyawan->id, ['jabatan' => 'Manager']);

        expect($result->jabatan)->toBe('Manager');
        expect(Karyawan::find($karyawan->id)->jabatan)->toBe('Manager');
    });

    it('should create an audit log entry on update', function () {
        $karyawan = createKaryawan([], $this->ptKlien);

        $this->service->update($karyawan->id, ['jabatan' => 'Supervisor']);

        $log = AuditLog::where('jenis_aktivitas', 'update_karyawan')
            ->where('model_id', $karyawan->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data_lama)->not->toBeNull()
            ->and($log->data_baru)->not->toBeNull();
    });
});

// ============================================================
// destroy() — Hapus karyawan
// ============================================================

describe('destroy', function () {
    it('should delete karyawan and return deleted=true', function () {
        $karyawan = createKaryawan([], $this->ptKlien);

        $result = $this->service->destroy($karyawan->id);

        expect($result['deleted'])->toBeTrue()
            ->and($result['warning'])->toBeNull();
        expect(Karyawan::find($karyawan->id))->toBeNull();
    });

    it('should return warning when karyawan has absensi data', function () {
        $karyawan = createKaryawan([], $this->ptKlien);

        // Create absensi record
        \App\Models\Absensi::create([
            'karyawan_id' => $karyawan->id,
            'tanggal' => '2025-06-01',
            'status_kehadiran' => 'Hadir',
            'jam_masuk' => '08:00',
            'jam_keluar' => '17:00',
            'jam_lembur' => 0,
        ]);

        $result = $this->service->destroy($karyawan->id);

        expect($result['deleted'])->toBeFalse()
            ->and($result['warning'])->toContain('data absensi');
    });

    it('should create an audit log entry on destroy', function () {
        $karyawan = createKaryawan([], $this->ptKlien);
        $karyawanId = $karyawan->id;

        $this->service->destroy($karyawanId);

        $log = AuditLog::where('jenis_aktivitas', 'delete_karyawan')
            ->where('model_id', $karyawanId)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data_lama)->not->toBeNull();
    });
});

// ============================================================
// index() — Filter karyawan
// ============================================================

describe('index — filtering', function () {
    beforeEach(function () {
        $ptKlien2 = PtKlien::create([
            'nama' => 'PT Lain',
            'alamat' => 'Jl. Lain',
            'telepon' => '021-9999999',
            'email' => 'lain@ptklien.com',
            'nama_pic' => 'PIC Lain',
            'nomor_kontrak' => 'KTR-002',
            'tgl_mulai' => '2025-01-01',
            'tgl_berakhir' => '2026-01-01',
            'fee_jasa' => 3000000,
        ]);

        createKaryawan(['nama_lengkap' => 'Budi Santoso', 'jabatan' => 'Staff IT', 'status_aktif' => true], $this->ptKlien);
        createKaryawan(['nama_lengkap' => 'Ani Wijaya', 'jabatan' => 'Manager', 'status_aktif' => true], $this->ptKlien);
        createKaryawan(['nama_lengkap' => 'Citra Dewi', 'jabatan' => 'Staff IT', 'status_aktif' => false], $ptKlien2);
        createKaryawan(['nama_lengkap' => 'Dedi Kurniawan', 'jabatan' => 'Supervisor', 'status_aktif' => true], $ptKlien2);
    });

    it('should return all karyawan when no filter applied', function () {
        $result = $this->service->index();
        expect($result->total())->toBe(4);
    });

    it('should filter by nama (partial match)', function () {
        $result = $this->service->index(['nama' => 'Budi']);
        expect($result->total())->toBe(1)
            ->and($result->first()->nama_lengkap)->toBe('Budi Santoso');
    });

    it('should filter by pt_klien_id', function () {
        $result = $this->service->index(['pt_klien_id' => $this->ptKlien->id]);
        expect($result->total())->toBe(2);
    });

    it('should filter by jabatan (partial match)', function () {
        $result = $this->service->index(['jabatan' => 'Staff IT']);
        expect($result->total())->toBe(2);
    });

    it('should filter by status_aktif true', function () {
        $result = $this->service->index(['status_aktif' => '1']);
        expect($result->total())->toBe(3);
    });

    it('should filter by status_aktif false', function () {
        $result = $this->service->index(['status_aktif' => '0']);
        expect($result->total())->toBe(1)
            ->and($result->first()->nama_lengkap)->toBe('Citra Dewi');
    });

    it('should combine multiple filters', function () {
        $result = $this->service->index([
            'jabatan' => 'Staff IT',
            'status_aktif' => '1',
        ]);
        expect($result->total())->toBe(1)
            ->and($result->first()->nama_lengkap)->toBe('Budi Santoso');
    });
});

// ============================================================
// checkRelatedData()
// ============================================================

describe('checkRelatedData', function () {
    it('should return false for both when no related data exists', function () {
        $karyawan = createKaryawan([], $this->ptKlien);

        $result = $this->service->checkRelatedData($karyawan->id);

        expect($result['has_absensi'])->toBeFalse()
            ->and($result['has_slip_gaji'])->toBeFalse();
    });

    it('should return has_absensi=true when absensi exists', function () {
        $karyawan = createKaryawan([], $this->ptKlien);

        \App\Models\Absensi::create([
            'karyawan_id' => $karyawan->id,
            'tanggal' => '2025-06-01',
            'status_kehadiran' => 'Hadir',
            'jam_masuk' => '08:00',
            'jam_keluar' => '17:00',
            'jam_lembur' => 0,
        ]);

        $result = $this->service->checkRelatedData($karyawan->id);

        expect($result['has_absensi'])->toBeTrue();
    });
});
