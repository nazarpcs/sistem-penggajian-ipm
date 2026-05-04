<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Payroll\KalkulatorGajiInterface;
use App\Domain\Payroll\KomponenGaji;
use App\Models\Karyawan;
use App\Models\KomponenSlipGaji;
use App\Models\KonfigurasiGaji;
use App\Models\PeriodePenggajian;
use App\Models\SlipGaji;
use App\Traits\HasAuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Service penggajian: hitung gaji, list slip gaji, detail slip gaji.
 *
 * Mengorkestrasi proses perhitungan gaji batch per periode dan PT Klien,
 * menyimpan hasil ke slip_gaji + komponen_slip_gaji dalam transaksi DB.
 *
 * @see Req 7.1, 7.4, 7.5, 7.6
 * @see Property 13: Kebenaran Rumus Perhitungan Gaji
 * @see Property 14: Immutability Data Gaji Historis
 * @see Property 18: Batas Minimum Gaji Bersih
 */
class PenggajianService
{
    use HasAuditLog;

    public function __construct(
        private readonly AbsensiService $absensiService,
        private readonly KalkulatorGajiInterface $kalkulator,
    ) {}

    /**
     * Hitung gaji untuk seluruh karyawan aktif pada periode dan PT Klien tertentu.
     *
     * Proses:
     * 1. Ambil rekap absensi dari AbsensiService
     * 2. Ambil konfigurasi gaji PT Klien
     * 3. Untuk setiap karyawan: buat KomponenGaji, panggil KalkulatorGaji::hitung()
     * 4. Simpan hasil ke slip_gaji + komponen_slip_gaji dalam DB::transaction
     * 5. Catat ke audit log
     *
     * Property 14: Slip gaji yang sudah tersimpan tidak akan berubah oleh
     * perubahan konfigurasi gaji setelahnya — nilai disnapshot saat perhitungan.
     *
     * @param int $periodeId ID periode penggajian
     * @param int $ptKlienId ID PT Klien
     * @return array{success: bool, message: string, total_karyawan?: int, warnings?: list<string>, code?: int}
     */
    public function hitungGaji(int $periodeId, int $ptKlienId): array
    {
        $periode = PeriodePenggajian::find($periodeId);
        if (!$periode) {
            return ['success' => false, 'message' => 'Periode penggajian tidak ditemukan.', 'code' => 404];
        }

        $konfigurasi = KonfigurasiGaji::where('pt_klien_id', $ptKlienId)->first();
        if (!$konfigurasi) {
            return ['success' => false, 'message' => 'Konfigurasi gaji untuk PT Klien ini belum diatur.', 'code' => 422];
        }

        // Ambil rekap absensi
        $rekapResult = $this->absensiService->rekap($periodeId, $ptKlienId);
        $rekapList = $rekapResult['rekap'];
        $warnings = $rekapResult['warnings'];

        if (empty($rekapList)) {
            return ['success' => false, 'message' => 'Tidak ada karyawan aktif untuk PT Klien ini.', 'code' => 422];
        }

        // Parse komponen tunjangan dari konfigurasi
        $komponenTunjangan = $this->parseTunjangan($konfigurasi->komponen_tunjangan);

        return DB::transaction(function () use ($periode, $ptKlienId, $konfigurasi, $rekapList, $komponenTunjangan, $warnings): array {
            $totalDiproses = 0;

            foreach ($rekapList as $rekap) {
                $karyawan = Karyawan::find($rekap['karyawan_id']);
                if (!$karyawan) {
                    $warnings[] = "Karyawan ID {$rekap['karyawan_id']} tidak ditemukan, dilewati.";
                    continue;
                }

                // Property 14: Cek apakah slip gaji sudah ada untuk kombinasi ini
                $existing = SlipGaji::where('karyawan_id', $karyawan->id)
                    ->where('periode_id', $periode->id)
                    ->first();

                if ($existing) {
                    $warnings[] = "Slip gaji untuk {$karyawan->nama_lengkap} pada periode ini sudah ada, dilewati.";
                    continue;
                }

                // Buat KomponenGaji DTO — snapshot nilai saat ini (Property 14)
                $komponen = new KomponenGaji(
                    gajiPokok: (int) $karyawan->gaji_pokok,
                    tunjangan: $komponenTunjangan,
                    jamLembur: (float) $rekap['total_jam_lembur'],
                    tarifLemburPerJam: (int) $konfigurasi->tarif_lembur_per_jam,
                    hariAlpha: (int) $rekap['total_hari_alpha'],
                    potonganPerHari: (int) $konfigurasi->potongan_per_hari,
                );

                // Hitung gaji via domain class (Property 13 & 18)
                $hasil = $this->kalkulator->hitung($komponen);

                // Simpan slip gaji — snapshot data, immutable (Property 14)
                $slipGaji = SlipGaji::create([
                    'karyawan_id' => $karyawan->id,
                    'periode_id' => $periode->id,
                    'gaji_pokok' => $hasil->gajiPokok,
                    'total_tunjangan' => $hasil->totalTunjangan,
                    'total_lembur' => $hasil->totalLembur,
                    'jam_lembur' => $hasil->jamLembur,
                    'total_potongan' => $hasil->totalPotongan,
                    'gaji_bersih' => $hasil->gajiBersih,
                    'status' => 'final',
                ]);

                // Simpan rincian komponen tunjangan
                foreach ($hasil->rincianTunjangan as $nama => $nilai) {
                    KomponenSlipGaji::create([
                        'slip_gaji_id' => $slipGaji->id,
                        'tipe' => 'tunjangan',
                        'nama_komponen' => $nama,
                        'nilai' => $nilai,
                    ]);
                }

                // Simpan komponen lembur jika ada
                if ($hasil->totalLembur > 0) {
                    KomponenSlipGaji::create([
                        'slip_gaji_id' => $slipGaji->id,
                        'tipe' => 'lembur',
                        'nama_komponen' => 'Lembur',
                        'nilai' => $hasil->totalLembur,
                    ]);
                }

                // Simpan komponen potongan jika ada
                if ($hasil->totalPotongan > 0) {
                    KomponenSlipGaji::create([
                        'slip_gaji_id' => $slipGaji->id,
                        'tipe' => 'potongan',
                        'nama_komponen' => 'Potongan Alpha',
                        'nilai' => $hasil->totalPotongan,
                    ]);
                }

                if ($hasil->peringatanNegatif) {
                    $warnings[] = "Gaji bersih {$karyawan->nama_lengkap} bernilai negatif, diset ke 0.";
                }

                $totalDiproses++;
            }

            // Audit log
            $this->logActivity(
                'hitung_gaji',
                [],
                [
                    'periode_id' => $periode->id,
                    'pt_klien_id' => $ptKlienId,
                    'total_karyawan' => $totalDiproses,
                ],
                'SlipGaji',
            );

            return [
                'success' => true,
                'message' => "Perhitungan gaji berhasil untuk {$totalDiproses} karyawan.",
                'total_karyawan' => $totalDiproses,
                'warnings' => $warnings,
            ];
        });
    }

    /**
     * List slip gaji dengan filter dan paginasi.
     *
     * @param array<string, mixed> $filters Filter: pt_klien_id, periode_id, karyawan_id, nama
     * @param int $perPage Jumlah data per halaman
     * @return LengthAwarePaginator
     */
    public function listSlipGaji(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = SlipGaji::query()
            ->with(['karyawan.ptKlien', 'periodePenggajian']);

        if (!empty($filters['pt_klien_id'])) {
            $query->whereHas('karyawan', function ($q) use ($filters) {
                $q->where('pt_klien_id', $filters['pt_klien_id']);
            });
        }

        if (!empty($filters['periode_id'])) {
            $query->where('periode_id', $filters['periode_id']);
        }

        if (!empty($filters['karyawan_id'])) {
            $query->where('karyawan_id', $filters['karyawan_id']);
        }

        if (!empty($filters['nama'])) {
            $query->whereHas('karyawan', function ($q) use ($filters) {
                $q->where('nama_lengkap', 'like', '%' . $filters['nama'] . '%');
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Detail slip gaji dengan komponen rincian.
     *
     * @param int $id ID slip gaji
     * @return array{success: bool, data?: SlipGaji, error?: string, code?: int}
     */
    public function detailSlipGaji(int $id): array
    {
        $slipGaji = SlipGaji::with(['karyawan.ptKlien', 'periodePenggajian', 'komponenSlipGaji'])
            ->find($id);

        if (!$slipGaji) {
            return ['success' => false, 'error' => 'Slip gaji tidak ditemukan.', 'code' => 404];
        }

        return ['success' => true, 'data' => $slipGaji];
    }

    /**
     * Parse komponen tunjangan dari JSON konfigurasi ke format [nama => nilai].
     *
     * @param array|null $komponenTunjangan JSON array dari konfigurasi gaji
     * @return array<string, int>
     */
    private function parseTunjangan(?array $komponenTunjangan): array
    {
        if (empty($komponenTunjangan)) {
            return [];
        }

        $result = [];
        foreach ($komponenTunjangan as $item) {
            if (isset($item['nama'], $item['nilai'])) {
                $result[$item['nama']] = (int) $item['nilai'];
            }
        }

        return $result;
    }
}
