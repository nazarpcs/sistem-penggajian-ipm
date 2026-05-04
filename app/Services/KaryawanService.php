<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Karyawan;
use App\Traits\HasAuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Service manajemen karyawan: CRUD, filter, soft delete dengan proteksi.
 *
 * @see Req 3.1, 3.4, 3.5, 3.6
 * @see Property 7: Penyimpanan Data Karyawan (Round-Trip)
 * @see Property 17: Invariant Audit Log
 */
class KaryawanService
{
    use HasAuditLog;

    /**
     * Daftar karyawan dengan filter dan paginasi.
     *
     * Filter: nama, pt_klien_id, jabatan, status_aktif.
     *
     * @param array<string, mixed> $filters
     * @see Req 3.6 (pencarian dan filter karyawan)
     * @see Property 10: Filter Karyawan Konsisten
     */
    public function index(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Karyawan::with('ptKlien', 'user');

        if (!empty($filters['nama'])) {
            $query->where('nama_lengkap', 'like', '%' . $filters['nama'] . '%');
        }

        if (!empty($filters['pt_klien_id'])) {
            $query->where('pt_klien_id', $filters['pt_klien_id']);
        }

        if (!empty($filters['jabatan'])) {
            $query->where('jabatan', 'like', '%' . $filters['jabatan'] . '%');
        }

        if (isset($filters['status_aktif']) && $filters['status_aktif'] !== '') {
            $query->where('status_aktif', (bool) $filters['status_aktif']);
        }

        return $query->orderBy('nama_lengkap')->paginate($perPage);
    }

    /**
     * Simpan karyawan baru.
     *
     * Mengekstrak email dari data input dan meneruskannya ke model
     * sebagai atribut sementara. KaryawanObserver akan membuat akun
     * User otomatis menggunakan email tersebut.
     *
     * @param array<string, mixed> $data
     *
     * @see Req 3.2, 3.3 (auto-create akun + notifikasi kredensial)
     * @see Property 8: Pembuatan Akun Otomatis Saat Karyawan Baru Dibuat
     */
    public function store(array $data): Karyawan
    {
        return DB::transaction(function () use ($data) {
            $email = $data['email'] ?? null;
            unset($data['email']);

            $karyawan = new Karyawan($data);

            if ($email && !isset($data['user_id'])) {
                $karyawan->setTemporaryEmail($email);
            }

            $karyawan->save();

            $this->logActivity(
                'create_karyawan',
                [],
                $karyawan->toArray(),
                'Karyawan',
                $karyawan->id,
            );

            return $karyawan;
        });
    }

    /**
     * Ambil detail karyawan beserta relasi.
     */
    public function show(int $id): Karyawan
    {
        return Karyawan::with('ptKlien', 'user')->findOrFail($id);
    }

    /**
     * Update data karyawan.
     *
     * @param array<string, mixed> $data
     * @see Req 3.4 (catat perubahan ke Audit_Log)
     */
    public function update(int $id, array $data): Karyawan
    {
        return DB::transaction(function () use ($id, $data) {
            $karyawan = Karyawan::findOrFail($id);
            $dataLama = $karyawan->toArray();

            $karyawan->update($data);
            $karyawan->refresh();

            $this->logActivity(
                'update_karyawan',
                $dataLama,
                $karyawan->toArray(),
                'Karyawan',
                $karyawan->id,
            );

            return $karyawan;
        });
    }

    /**
     * Hapus karyawan dengan proteksi data terkait.
     *
     * Jika karyawan memiliki data absensi atau slip gaji:
     * - force=false → return peringatan tanpa menghapus, minta konfirmasi
     * - force=true  → lanjutkan penghapusan dan catat ke audit log
     *
     * Jika tidak ada data terkait, langsung hapus.
     *
     * @param bool $force Paksa hapus meskipun ada data terkait
     * @return array{deleted: bool, warning: string|null, has_related_data: bool}
     *
     * @see Req 3.5 (peringatan sebelum hapus karyawan dengan data terkait)
     * @see Property 17: Invariant Audit Log
     */
    public function destroy(int $id, bool $force = false): array
    {
        return DB::transaction(function () use ($id, $force) {
            $karyawan = Karyawan::findOrFail($id);

            $hasAbsensi = $karyawan->absensi()->exists();
            $hasSlipGaji = $karyawan->slipGaji()->exists();
            $hasRelatedData = $hasAbsensi || $hasSlipGaji;

            // Jika ada data terkait dan force bukan true, kembalikan peringatan
            if ($hasRelatedData && !$force) {
                $parts = [];
                if ($hasAbsensi) {
                    $parts[] = 'data absensi';
                }
                if ($hasSlipGaji) {
                    $parts[] = 'slip gaji';
                }
                $warning = 'Karyawan "' . $karyawan->nama_lengkap . '" memiliki '
                    . implode(' dan ', $parts)
                    . ' yang terkait. Apakah Anda yakin ingin menghapus?';

                return [
                    'deleted' => false,
                    'warning' => $warning,
                    'has_related_data' => true,
                ];
            }

            // Lanjutkan penghapusan (force=true atau tidak ada data terkait)
            $dataLama = $karyawan->toArray();

            // Nonaktifkan akun user terkait sebelum hapus karyawan
            $user = $karyawan->user;
            if ($user) {
                $user->update(['is_active' => false]);
            }

            $karyawan->delete();

            // Audit log dicatat di observer (deleting event),
            // tapi kita juga catat di service level untuk konsistensi
            $this->logActivity(
                'delete_karyawan',
                $dataLama,
                ['force' => $force, 'has_related_data' => $hasRelatedData],
                'Karyawan',
                $id,
            );

            return [
                'deleted' => true,
                'warning' => null,
                'has_related_data' => $hasRelatedData,
            ];
        });
    }

    /**
     * Cek apakah karyawan memiliki data terkait (absensi/slip gaji).
     *
     * Digunakan oleh controller untuk pengecekan via AJAX sebelum konfirmasi hapus.
     *
     * @return array{has_absensi: bool, has_slip_gaji: bool, nama_lengkap: string}
     */
    public function checkRelatedData(int $id): array
    {
        $karyawan = Karyawan::findOrFail($id);

        return [
            'has_absensi' => $karyawan->absensi()->exists(),
            'has_slip_gaji' => $karyawan->slipGaji()->exists(),
            'nama_lengkap' => $karyawan->nama_lengkap,
        ];
    }
}
