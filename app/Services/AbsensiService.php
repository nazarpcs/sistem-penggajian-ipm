<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Validation\AbsensiValidator;
use App\Domain\Validation\ExcelAbsensiValidator;
use App\Jobs\ProsesImportAbsensi;
use App\Models\Absensi;
use App\Models\Karyawan;
use App\Models\KonfigurasiGaji;
use App\Models\PeriodePenggajian;
use App\Traits\HasAuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Service absensi: input manual, import Excel, rekap, kunci periode.
 *
 * Mengorkestrasi logika bisnis absensi, memanggil domain validator,
 * dan mengelola transaksi database.
 *
 * @see Req 5.1, 5.6, 5.7, 6.1-6.7
 */
class AbsensiService
{
    use HasAuditLog;

    /**
     * List absensi dengan filter dan paginasi.
     *
     * @param array<string, mixed> $filters Filter: karyawan_id, pt_klien_id, tanggal_mulai, tanggal_selesai, status_kehadiran
     * @param int $perPage Jumlah data per halaman
     * @return LengthAwarePaginator
     */
    public function index(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Absensi::query()->with('karyawan.ptKlien');

        if (!empty($filters['karyawan_id'])) {
            $query->where('karyawan_id', $filters['karyawan_id']);
        }

        if (!empty($filters['pt_klien_id'])) {
            $query->whereHas('karyawan', function ($q) use ($filters) {
                $q->where('pt_klien_id', $filters['pt_klien_id']);
            });
        }

        if (!empty($filters['tanggal_mulai'])) {
            $query->where('tanggal', '>=', $filters['tanggal_mulai']);
        }

        if (!empty($filters['tanggal_selesai'])) {
            $query->where('tanggal', '<=', $filters['tanggal_selesai']);
        }

        if (!empty($filters['status_kehadiran'])) {
            $query->where('status_kehadiran', $filters['status_kehadiran']);
        }

        return $query->orderBy('tanggal', 'desc')
            ->orderBy('karyawan_id')
            ->paginate($perPage);
    }

    /**
     * Simpan absensi manual. Cek duplikasi karyawan_id+tanggal.
     *
     * @param array<string, mixed> $data Data absensi
     * @return array{success: bool, data?: Absensi, error?: string, code?: int}
     */
    public function store(array $data): array
    {
        // Cek duplikasi
        $exists = Absensi::where('karyawan_id', $data['karyawan_id'])
            ->where('tanggal', $data['tanggal'])
            ->exists();

        if ($exists) {
            return [
                'success' => false,
                'error' => "Data absensi untuk karyawan ini pada tanggal {$data['tanggal']} sudah ada.",
                'code' => 409,
            ];
        }

        return DB::transaction(function () use ($data): array {
            // Hitung jam lembur jika status Hadir
            $data['jam_lembur'] = $this->hitungJamLembur(
                (int) $data['karyawan_id'],
                $data['status_kehadiran'],
                $data['jam_masuk'] ?? null,
                $data['jam_keluar'] ?? null,
            );

            $absensi = Absensi::create($data);

            $this->logActivity(
                'create_absensi',
                [],
                $absensi->toArray(),
                'Absensi',
                $absensi->id,
            );

            return ['success' => true, 'data' => $absensi];
        });
    }

    /**
     * Update absensi + audit log.
     *
     * @param int $id ID absensi
     * @param array<string, mixed> $data Data absensi baru
     * @return array{success: bool, data?: Absensi, error?: string, code?: int}
     */
    public function update(int $id, array $data): array
    {
        $absensi = Absensi::find($id);

        if (!$absensi) {
            return [
                'success' => false,
                'error' => 'Data absensi tidak ditemukan.',
                'code' => 404,
            ];
        }

        // Cek apakah periode terkunci
        $periodeTerkunci = $this->isPeriodeTerkunci($absensi->tanggal);
        if ($periodeTerkunci) {
            return [
                'success' => false,
                'error' => 'Periode absensi sudah terkunci. Tidak dapat mengubah data.',
                'code' => 422,
            ];
        }

        // Cek duplikasi jika karyawan_id atau tanggal berubah
        if (
            (isset($data['karyawan_id']) && (int) $data['karyawan_id'] !== $absensi->karyawan_id)
            || (isset($data['tanggal']) && $data['tanggal'] !== $absensi->tanggal->format('Y-m-d'))
        ) {
            $karyawanId = $data['karyawan_id'] ?? $absensi->karyawan_id;
            $tanggal = $data['tanggal'] ?? $absensi->tanggal->format('Y-m-d');

            $exists = Absensi::where('karyawan_id', $karyawanId)
                ->where('tanggal', $tanggal)
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                return [
                    'success' => false,
                    'error' => "Data absensi untuk karyawan ini pada tanggal {$tanggal} sudah ada.",
                    'code' => 409,
                ];
            }
        }

        return DB::transaction(function () use ($absensi, $data): array {
            $dataLama = $absensi->toArray();

            // Hitung ulang jam lembur
            $statusKehadiran = $data['status_kehadiran'] ?? $absensi->status_kehadiran;
            $karyawanId = (int) ($data['karyawan_id'] ?? $absensi->karyawan_id);
            $jamMasuk = $data['jam_masuk'] ?? $absensi->jam_masuk;
            $jamKeluar = $data['jam_keluar'] ?? $absensi->jam_keluar;

            $data['jam_lembur'] = $this->hitungJamLembur(
                $karyawanId,
                $statusKehadiran,
                $jamMasuk,
                $jamKeluar,
            );

            $absensi->update($data);

            $this->logActivity(
                'update_absensi',
                $dataLama,
                $absensi->fresh()->toArray(),
                'Absensi',
                $absensi->id,
            );

            return ['success' => true, 'data' => $absensi->fresh()];
        });
    }

    /**
     * Import absensi dari file Excel.
     * Validasi sinkron via AbsensiValidator, jika valid dispatch ProsesImportAbsensi job.
     *
     * @param UploadedFile $file File Excel
     * @return array{success: bool, message?: string, errors?: array, total_baris?: int, code?: int}
     */
    public function importExcel(UploadedFile $file): array
    {
        // Step 1: Validasi file Excel (format, header, parse)
        $excelValidator = new ExcelAbsensiValidator();
        $parseResult = $excelValidator->validasiDanParse($file);

        if (!$parseResult['valid']) {
            return [
                'success' => false,
                'message' => 'Validasi file Excel gagal.',
                'errors' => $parseResult['errors'],
                'code' => 422,
            ];
        }

        // Step 2: Validasi data via AbsensiValidator (sinkron)
        $duplikasiChecker = function (int $karyawanId, string $tanggal): bool {
            return Absensi::where('karyawan_id', $karyawanId)
                ->where('tanggal', $tanggal)
                ->exists();
        };

        $validator = new AbsensiValidator($duplikasiChecker);
        $validationResult = $validator->validasiBulk($parseResult['data']);

        if (!$validationResult['valid']) {
            return [
                'success' => false,
                'message' => 'Validasi data absensi gagal. Seluruh file ditolak.',
                'errors' => $validationResult['errors'],
                'total_baris' => $validationResult['total_baris'],
                'code' => 422,
            ];
        }

        // Step 3: Dispatch job untuk penyimpanan async
        ProsesImportAbsensi::dispatch($parseResult['data']);

        $this->logActivity(
            'import_absensi',
            [],
            ['total_baris' => $validationResult['total_baris'], 'file' => $file->getClientOriginalName()],
            'Absensi',
        );

        return [
            'success' => true,
            'message' => "Validasi berhasil. {$validationResult['total_baris']} baris data sedang diproses.",
            'total_baris' => $validationResult['total_baris'],
        ];
    }

    /**
     * Rekap absensi per periode dan PT Klien.
     *
     * @param int $periodeId ID periode penggajian
     * @param int $ptKlienId ID PT Klien
     * @return array{rekap: array<int, array<string, mixed>>, warnings: list<string>}
     */
    public function rekap(int $periodeId, int $ptKlienId): array
    {
        $periode = PeriodePenggajian::findOrFail($periodeId);

        // Ambil semua karyawan aktif di PT Klien
        $karyawanList = Karyawan::where('pt_klien_id', $ptKlienId)
            ->where('status_aktif', true)
            ->get();

        $warnings = [];
        $rekap = [];

        foreach ($karyawanList as $karyawan) {
            $absensiData = Absensi::where('karyawan_id', $karyawan->id)
                ->whereBetween('tanggal', [$periode->tanggal_mulai, $periode->tanggal_selesai])
                ->get();

            if ($absensiData->isEmpty()) {
                $warnings[] = "Karyawan {$karyawan->nama_lengkap} (ID: {$karyawan->id}) tidak memiliki data absensi pada periode ini.";
            }

            $totalHadir = $absensiData->where('status_kehadiran', 'Hadir')->count();
            $totalIzin = $absensiData->where('status_kehadiran', 'Izin')->count();
            $totalSakit = $absensiData->where('status_kehadiran', 'Sakit')->count();
            $totalAlpha = $absensiData->where('status_kehadiran', 'Alpha')->count();
            $totalJamLembur = (float) $absensiData->sum('jam_lembur');

            $rekap[] = [
                'karyawan_id' => $karyawan->id,
                'nama_lengkap' => $karyawan->nama_lengkap,
                'nik' => $karyawan->nik,
                'total_hari_hadir' => $totalHadir,
                'total_hari_izin' => $totalIzin,
                'total_hari_sakit' => $totalSakit,
                'total_hari_alpha' => $totalAlpha,
                'total_jam_lembur' => $totalJamLembur,
            ];
        }

        return [
            'rekap' => $rekap,
            'warnings' => $warnings,
        ];
    }

    /**
     * Kunci periode absensi — set status='terkunci'.
     *
     * @param int $periodeId ID periode penggajian
     * @return array{success: bool, message: string, code?: int}
     */
    public function kunciPeriode(int $periodeId): array
    {
        $periode = PeriodePenggajian::find($periodeId);

        if (!$periode) {
            return ['success' => false, 'message' => 'Periode penggajian tidak ditemukan.', 'code' => 404];
        }

        if ($periode->status === 'terkunci') {
            return ['success' => false, 'message' => 'Periode sudah terkunci.', 'code' => 422];
        }

        return DB::transaction(function () use ($periode): array {
            $dataLama = $periode->toArray();

            $periode->update(['status' => 'terkunci']);

            $this->logActivity(
                'kunci_periode',
                $dataLama,
                $periode->fresh()->toArray(),
                'PeriodePenggajian',
                $periode->id,
            );

            return ['success' => true, 'message' => 'Periode berhasil dikunci.'];
        });
    }

    /**
     * Buka kunci periode absensi — set status='aktif', catat alasan ke audit log.
     *
     * @param int $periodeId ID periode penggajian
     * @param string $alasan Alasan pembukaan kunci
     * @return array{success: bool, message: string, code?: int}
     */
    public function bukaKunciPeriode(int $periodeId, string $alasan): array
    {
        $periode = PeriodePenggajian::find($periodeId);

        if (!$periode) {
            return ['success' => false, 'message' => 'Periode penggajian tidak ditemukan.', 'code' => 404];
        }

        if ($periode->status === 'aktif') {
            return ['success' => false, 'message' => 'Periode sudah dalam status aktif.', 'code' => 422];
        }

        return DB::transaction(function () use ($periode, $alasan): array {
            $dataLama = $periode->toArray();

            $periode->update(['status' => 'aktif']);

            $this->logActivity(
                'buka_kunci_periode',
                $dataLama,
                array_merge($periode->fresh()->toArray(), ['alasan' => $alasan]),
                'PeriodePenggajian',
                $periode->id,
            );

            return ['success' => true, 'message' => 'Periode berhasil dibuka kuncinya.'];
        });
    }

    /**
     * Hitung jam lembur berdasarkan jam_keluar - jam_kerja_normal per PT Klien.
     *
     * @param int $karyawanId ID karyawan
     * @param string $statusKehadiran Status kehadiran
     * @param string|null $jamMasuk Jam masuk (H:i)
     * @param string|null $jamKeluar Jam keluar (H:i)
     * @return float Jam lembur (0 jika tidak lembur)
     */
    private function hitungJamLembur(
        int $karyawanId,
        string $statusKehadiran,
        ?string $jamMasuk,
        ?string $jamKeluar,
    ): float {
        if ($statusKehadiran !== 'Hadir' || $jamMasuk === null || $jamKeluar === null) {
            return 0.0;
        }

        $karyawan = Karyawan::find($karyawanId);
        if (!$karyawan) {
            return 0.0;
        }

        $konfigurasi = KonfigurasiGaji::where('pt_klien_id', $karyawan->pt_klien_id)->first();
        if (!$konfigurasi) {
            return 0.0;
        }

        // Hitung total jam kerja dari jam_masuk dan jam_keluar
        $masuk = strtotime($jamMasuk);
        $keluar = strtotime($jamKeluar);

        if ($masuk === false || $keluar === false || $keluar <= $masuk) {
            return 0.0;
        }

        $totalJamKerja = ($keluar - $masuk) / 3600;
        $jamKerjaNormal = (float) $konfigurasi->jam_kerja_normal;

        $jamLembur = $totalJamKerja - $jamKerjaNormal;

        return $jamLembur > 0 ? round($jamLembur, 2) : 0.0;
    }

    /**
     * Cek apakah tanggal berada dalam periode yang terkunci.
     *
     * @param \Illuminate\Support\Carbon $tanggal Tanggal absensi
     * @return bool True jika periode terkunci
     */
    private function isPeriodeTerkunci(\Illuminate\Support\Carbon $tanggal): bool
    {
        return PeriodePenggajian::where('status', 'terkunci')
            ->where('tanggal_mulai', '<=', $tanggal)
            ->where('tanggal_selesai', '>=', $tanggal)
            ->exists();
    }
}
