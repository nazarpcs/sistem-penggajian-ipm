<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Karyawan;
use App\Models\PtKlien;
use App\Traits\HasAuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Service manajemen PT Klien: CRUD, filter, karyawan per klien.
 *
 * @see Req 4.1, 4.2, 4.3
 * @see Property 17: Invariant Audit Log
 */
class PtKlienService
{
    use HasAuditLog;

    /**
     * Daftar PT Klien dengan filter dan paginasi.
     *
     * Filter: nama, status_kontrak (aktif/kadaluarsa).
     *
     * @param array<string, mixed> $filters
     */
    public function index(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = PtKlien::query();

        if (!empty($filters['nama'])) {
            $query->where('nama', 'like', '%' . $filters['nama'] . '%');
        }

        if (!empty($filters['status_kontrak'])) {
            if ($filters['status_kontrak'] === 'aktif') {
                $query->where('tgl_berakhir', '>=', now()->toDateString());
            } elseif ($filters['status_kontrak'] === 'kadaluarsa') {
                $query->where('tgl_berakhir', '<', now()->toDateString());
            }
        }

        return $query->withCount('karyawan')->orderBy('nama')->paginate($perPage);
    }

    /**
     * Simpan PT Klien baru + audit log.
     *
     * @param array<string, mixed> $data
     * @see Req 4.2
     */
    public function store(array $data): PtKlien
    {
        return DB::transaction(function () use ($data): PtKlien {
            $ptKlien = PtKlien::create($data);

            $this->logActivity(
                'create_pt_klien',
                [],
                $ptKlien->toArray(),
                'PtKlien',
                $ptKlien->id,
            );

            return $ptKlien;
        });
    }

    /**
     * Detail PT Klien dengan relasi karyawan dan konfigurasi gaji.
     */
    public function show(int $id): PtKlien
    {
        return PtKlien::with(['karyawan', 'konfigurasiGaji'])->findOrFail($id);
    }

    /**
     * Update data PT Klien + audit log.
     *
     * @param array<string, mixed> $data
     * @see Req 4.2
     */
    public function update(int $id, array $data): PtKlien
    {
        return DB::transaction(function () use ($id, $data): PtKlien {
            $ptKlien = PtKlien::findOrFail($id);
            $dataLama = $ptKlien->toArray();

            $ptKlien->update($data);
            $ptKlien->refresh();

            $this->logActivity(
                'update_pt_klien',
                $dataLama,
                $ptKlien->toArray(),
                'PtKlien',
                $ptKlien->id,
            );

            return $ptKlien;
        });
    }

    /**
     * Daftar karyawan yang terdaftar di bawah PT Klien tertentu.
     *
     * @see Req 4.3
     */
    public function karyawanPerKlien(int $id): LengthAwarePaginator
    {
        // Pastikan PT Klien ada
        PtKlien::findOrFail($id);

        return Karyawan::with('user')
            ->where('pt_klien_id', $id)
            ->orderBy('nama_lengkap')
            ->paginate(15);
    }
}
