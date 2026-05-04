<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Trait HasAuditLog — menyediakan method untuk mencatat aktivitas ke audit log.
 *
 * Digunakan oleh Service dan Controller untuk mencatat operasi kritis:
 * login/logout, CRUD karyawan, CRUD PT Klien, absensi,
 * perhitungan gaji, invoice approval/rejection.
 *
 * @see Property 17: Invariant Audit Log
 */
trait HasAuditLog
{
    /**
     * Catat aktivitas ke audit log.
     *
     * @param string $jenisAktivitas Jenis aktivitas (login, logout, create, update, delete, dll)
     * @param array<string, mixed> $dataLama Data sebelum perubahan
     * @param array<string, mixed> $dataBaru Data setelah perubahan
     * @param string|null $modelTipe Nama class model yang terkait (e.g. 'Karyawan')
     * @param int|null $modelId ID record model yang terkait
     */
    public function logActivity(
        string $jenisAktivitas,
        array $dataLama = [],
        array $dataBaru = [],
        ?string $modelTipe = null,
        ?int $modelId = null,
    ): void {
        $user = Auth::user();

        AuditLog::create([
            'user_id' => $user?->id,
            'role_pengguna' => $user?->role ?? 'guest',
            'jenis_aktivitas' => $jenisAktivitas,
            'model_tipe' => $modelTipe,
            'model_id' => $modelId,
            'data_lama' => !empty($dataLama) ? $dataLama : null,
            'data_baru' => !empty($dataBaru) ? $dataBaru : null,
            'ip_address' => Request::ip(),
            'created_at' => now(),
        ]);
    }
}
