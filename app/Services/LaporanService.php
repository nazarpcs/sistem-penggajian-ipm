<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Absensi;
use App\Models\Invoice;
use App\Models\SlipGaji;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Service laporan: absensi, penggajian, invoice.
 *
 * Menyediakan query data dengan filter untuk generate laporan,
 * digunakan oleh LaporanController untuk tampilan, export PDF, dan Excel.
 *
 * @see Req 10.3-10.6
 */
class LaporanService
{
    use HasAuditLog;

    /**
     * Ambil data laporan absensi dengan filter.
     *
     * Filter yang didukung:
     * - pt_klien_id: filter berdasarkan PT Klien
     * - karyawan_id: filter berdasarkan karyawan tertentu
     * - tanggal_mulai: batas awal rentang tanggal
     * - tanggal_selesai: batas akhir rentang tanggal
     *
     * @param array<string, mixed> $filters
     * @return Collection
     */
    public function laporanAbsensi(array $filters): Collection
    {
        $query = Absensi::query()
            ->with(['karyawan', 'karyawan.ptKlien'])
            ->join('karyawan', 'absensi.karyawan_id', '=', 'karyawan.id');

        if (!empty($filters['pt_klien_id'])) {
            $query->where('karyawan.pt_klien_id', $filters['pt_klien_id']);
        }

        if (!empty($filters['karyawan_id'])) {
            $query->where('absensi.karyawan_id', $filters['karyawan_id']);
        }

        if (!empty($filters['tanggal_mulai'])) {
            $query->where('absensi.tanggal', '>=', $filters['tanggal_mulai']);
        }

        if (!empty($filters['tanggal_selesai'])) {
            $query->where('absensi.tanggal', '<=', $filters['tanggal_selesai']);
        }

        return $query
            ->select('absensi.*')
            ->orderBy('absensi.tanggal', 'desc')
            ->orderBy('karyawan.nama_lengkap', 'asc')
            ->get();
    }

    /**
     * Hitung rekap absensi per karyawan dari data laporan absensi.
     *
     * Mengelompokkan data absensi per karyawan dan menghitung:
     * total hadir, izin, sakit, alpha, dan jam lembur.
     *
     * @param Collection $dataAbsensi Hasil dari laporanAbsensi()
     * @return Collection
     */
    public function rekapAbsensiPerKaryawan(Collection $dataAbsensi): Collection
    {
        return $dataAbsensi->groupBy('karyawan_id')->map(function (Collection $items) {
            $karyawan = $items->first()->karyawan;

            return (object) [
                'karyawan' => $karyawan,
                'total_hadir' => $items->where('status_kehadiran', 'Hadir')->count(),
                'total_izin' => $items->where('status_kehadiran', 'Izin')->count(),
                'total_sakit' => $items->where('status_kehadiran', 'Sakit')->count(),
                'total_alpha' => $items->where('status_kehadiran', 'Alpha')->count(),
                'total_jam_lembur' => (float) $items->sum('jam_lembur'),
            ];
        })->values();
    }

    /**
     * Ambil data laporan penggajian dengan filter.
     *
     * Filter yang didukung:
     * - pt_klien_id: filter berdasarkan PT Klien
     * - periode_id: filter berdasarkan periode penggajian
     *
     * @param array<string, mixed> $filters
     * @return Collection
     */
    public function laporanPenggajian(array $filters): Collection
    {
        $query = SlipGaji::query()
            ->with(['karyawan', 'karyawan.ptKlien', 'periodePenggajian', 'komponenSlipGaji']);

        if (!empty($filters['pt_klien_id'])) {
            $query->whereHas('karyawan', function (Builder $q) use ($filters): void {
                $q->where('pt_klien_id', $filters['pt_klien_id']);
            });
        }

        if (!empty($filters['periode_id'])) {
            $query->where('periode_id', $filters['periode_id']);
        }

        return $query
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Ambil data laporan invoice dengan filter.
     *
     * Filter yang didukung:
     * - pt_klien_id: filter berdasarkan PT Klien
     * - periode_id: filter berdasarkan periode penggajian
     * - status: filter berdasarkan status invoice (menunggu_approval, disetujui, ditolak)
     *
     * @param array<string, mixed> $filters
     * @return Collection
     */
    public function laporanInvoice(array $filters): Collection
    {
        $query = Invoice::query()
            ->with(['ptKlien', 'periodePenggajian', 'approvedByUser', 'rejectedByUser']);

        if (!empty($filters['pt_klien_id'])) {
            $query->where('pt_klien_id', $filters['pt_klien_id']);
        }

        if (!empty($filters['periode_id'])) {
            $query->where('periode_id', $filters['periode_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Hitung ringkasan laporan penggajian.
     *
     * @param Collection $dataSlipGaji Hasil dari laporanPenggajian()
     * @return object{total_gaji_pokok: float, total_tunjangan: float, total_lembur: float, total_potongan: float, total_gaji_bersih: float, jumlah_karyawan: int}
     */
    public function ringkasanPenggajian(Collection $dataSlipGaji): object
    {
        return (object) [
            'total_gaji_pokok' => (float) $dataSlipGaji->sum('gaji_pokok'),
            'total_tunjangan' => (float) $dataSlipGaji->sum('total_tunjangan'),
            'total_lembur' => (float) $dataSlipGaji->sum('total_lembur'),
            'total_potongan' => (float) $dataSlipGaji->sum('total_potongan'),
            'total_gaji_bersih' => (float) $dataSlipGaji->sum('gaji_bersih'),
            'jumlah_karyawan' => $dataSlipGaji->count(),
        ];
    }

    /**
     * Hitung ringkasan laporan invoice.
     *
     * @param Collection $dataInvoice Hasil dari laporanInvoice()
     * @return object{total_subtotal_gaji: float, total_fee_jasa: float, total_pajak: float, total_tagihan: float, jumlah_invoice: int}
     */
    public function ringkasanInvoice(Collection $dataInvoice): object
    {
        return (object) [
            'total_subtotal_gaji' => (float) $dataInvoice->sum('subtotal_gaji'),
            'total_fee_jasa' => (float) $dataInvoice->sum('fee_jasa'),
            'total_pajak' => (float) $dataInvoice->sum('pajak'),
            'total_tagihan' => (float) $dataInvoice->sum('total_tagihan'),
            'jumlah_invoice' => $dataInvoice->count(),
        ];
    }
}
