<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Absensi;
use App\Models\Karyawan;
use App\Models\KonfigurasiGaji;
use App\Traits\HasAuditLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Job untuk proses import absensi dari file Excel secara asinkron.
 *
 * Validasi dilakukan secara sinkron SEBELUM job ini di-dispatch.
 * Job ini hanya menyimpan data yang sudah tervalidasi ke database.
 *
 * @see Property 20: Validasi Sinkron Sebelum Import Async
 */
class ProsesImportAbsensi implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HasAuditLog;

    /**
     * Jumlah percobaan ulang jika job gagal.
     */
    public int $tries = 3;

    /**
     * @param array<int, array<string, mixed>> $rows Data absensi yang sudah tervalidasi
     */
    public function __construct(
        private readonly array $rows,
    ) {
    }

    /**
     * Proses penyimpanan data absensi ke database dalam batch.
     */
    public function handle(): void
    {
        DB::transaction(function (): void {
            $inserted = 0;

            foreach ($this->rows as $row) {
                $karyawanId = (int) $row['karyawan_id'];
                $statusKehadiran = $row['status_kehadiran'];
                $jamMasuk = !empty($row['jam_masuk']) ? $row['jam_masuk'] : null;
                $jamKeluar = !empty($row['jam_keluar']) ? $row['jam_keluar'] : null;

                // Hitung jam lembur otomatis
                $jamLembur = $this->hitungJamLembur(
                    $karyawanId,
                    $statusKehadiran,
                    $jamMasuk,
                    $jamKeluar,
                );

                Absensi::create([
                    'karyawan_id' => $karyawanId,
                    'tanggal' => $row['tanggal'],
                    'status_kehadiran' => $statusKehadiran,
                    'jam_masuk' => $jamMasuk,
                    'jam_keluar' => $jamKeluar,
                    'jam_lembur' => $jamLembur,
                    'keterangan' => !empty($row['keterangan']) ? $row['keterangan'] : null,
                ]);

                $inserted++;
            }

            Log::info("ProsesImportAbsensi: {$inserted} baris berhasil disimpan.");
        });
    }

    /**
     * Hitung jam lembur berdasarkan jam_keluar - jam_kerja_normal per PT Klien.
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
}
