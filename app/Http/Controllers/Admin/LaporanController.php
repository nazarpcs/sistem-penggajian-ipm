<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Exports\LaporanAbsensiExport;
use App\Exports\LaporanInvoiceExport;
use App\Exports\LaporanPenggajianExport;
use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\PeriodePenggajian;
use App\Models\PtKlien;
use App\Services\LaporanService;
use App\Traits\HasAuditLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller laporan — absensi, penggajian, invoice.
 *
 * Menyediakan tampilan laporan dengan filter dan export PDF/Excel.
 * Digunakan oleh Admin dan Pemilik PT (via route group masing-masing).
 *
 * @see Req 10.3-10.6
 */
class LaporanController extends Controller
{
    use HasAuditLog;

    public function __construct(
        private readonly LaporanService $laporanService,
    ) {}

    /**
     * Laporan absensi — tampilan, export PDF, atau export Excel.
     *
     * Filter: pt_klien_id, karyawan_id, tanggal_mulai, tanggal_selesai
     * Export: ?export=pdf atau ?export=excel
     */
    public function absensi(Request $request): View|Response|BinaryFileResponse
    {
        $filters = $request->only(['pt_klien_id', 'karyawan_id', 'tanggal_mulai', 'tanggal_selesai']);
        $dataAbsensi = $this->laporanService->laporanAbsensi($filters);
        $rekapPerKaryawan = $this->laporanService->rekapAbsensiPerKaryawan($dataAbsensi);

        // Export PDF
        if ($request->query('export') === 'pdf') {
            $this->logActivity('export_laporan_absensi_pdf', [], $filters, 'Laporan', null);

            $pdf = Pdf::loadView('pdf.laporan-absensi', [
                'dataAbsensi' => $dataAbsensi,
                'rekapPerKaryawan' => $rekapPerKaryawan,
                'filters' => $filters,
                'tanggalCetak' => now()->translatedFormat('d F Y'),
            ]);

            return $pdf->download('laporan-absensi.pdf');
        }

        // Export Excel
        if ($request->query('export') === 'excel') {
            $this->logActivity('export_laporan_absensi_excel', [], $filters, 'Laporan', null);

            return Excel::download(
                new LaporanAbsensiExport($dataAbsensi, $rekapPerKaryawan),
                'laporan-absensi.xlsx',
            );
        }

        // Data untuk dropdown filter
        $daftarPtKlien = PtKlien::orderBy('nama')->get();
        $daftarKaryawan = Karyawan::where('status_aktif', true)->orderBy('nama_lengkap')->get();

        return view('admin.laporan.absensi', compact(
            'dataAbsensi',
            'rekapPerKaryawan',
            'daftarPtKlien',
            'daftarKaryawan',
            'filters',
        ));
    }

    /**
     * Laporan penggajian — tampilan, export PDF, atau export Excel.
     *
     * Filter: pt_klien_id, periode_id
     * Export: ?export=pdf atau ?export=excel
     */
    public function penggajian(Request $request): View|Response|BinaryFileResponse
    {
        $filters = $request->only(['pt_klien_id', 'periode_id']);
        $dataSlipGaji = $this->laporanService->laporanPenggajian($filters);
        $ringkasan = $this->laporanService->ringkasanPenggajian($dataSlipGaji);

        // Export PDF
        if ($request->query('export') === 'pdf') {
            $this->logActivity('export_laporan_penggajian_pdf', [], $filters, 'Laporan', null);

            $pdf = Pdf::loadView('pdf.laporan-penggajian', [
                'dataSlipGaji' => $dataSlipGaji,
                'ringkasan' => $ringkasan,
                'filters' => $filters,
                'tanggalCetak' => now()->translatedFormat('d F Y'),
            ]);

            return $pdf->download('laporan-penggajian.pdf');
        }

        // Export Excel
        if ($request->query('export') === 'excel') {
            $this->logActivity('export_laporan_penggajian_excel', [], $filters, 'Laporan', null);

            return Excel::download(
                new LaporanPenggajianExport($dataSlipGaji, $ringkasan),
                'laporan-penggajian.xlsx',
            );
        }

        // Data untuk dropdown filter
        $daftarPtKlien = PtKlien::orderBy('nama')->get();
        $daftarPeriode = PeriodePenggajian::orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->get();

        return view('admin.laporan.penggajian', compact(
            'dataSlipGaji',
            'ringkasan',
            'daftarPtKlien',
            'daftarPeriode',
            'filters',
        ));
    }

    /**
     * Laporan invoice — tampilan, export PDF, atau export Excel.
     *
     * Filter: pt_klien_id, periode_id, status
     * Export: ?export=pdf atau ?export=excel
     */
    public function invoice(Request $request): View|Response|BinaryFileResponse
    {
        $filters = $request->only(['pt_klien_id', 'periode_id', 'status']);
        $dataInvoice = $this->laporanService->laporanInvoice($filters);
        $ringkasan = $this->laporanService->ringkasanInvoice($dataInvoice);

        // Export PDF
        if ($request->query('export') === 'pdf') {
            $this->logActivity('export_laporan_invoice_pdf', [], $filters, 'Laporan', null);

            $pdf = Pdf::loadView('pdf.laporan-invoice', [
                'dataInvoice' => $dataInvoice,
                'ringkasan' => $ringkasan,
                'filters' => $filters,
                'tanggalCetak' => now()->translatedFormat('d F Y'),
            ]);

            return $pdf->download('laporan-invoice.pdf');
        }

        // Export Excel
        if ($request->query('export') === 'excel') {
            $this->logActivity('export_laporan_invoice_excel', [], $filters, 'Laporan', null);

            return Excel::download(
                new LaporanInvoiceExport($dataInvoice, $ringkasan),
                'laporan-invoice.xlsx',
            );
        }

        // Data untuk dropdown filter
        $daftarPtKlien = PtKlien::orderBy('nama')->get();
        $daftarPeriode = PeriodePenggajian::orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->get();
        $daftarStatus = ['menunggu_approval', 'disetujui', 'ditolak'];

        return view('admin.laporan.invoice', compact(
            'dataInvoice',
            'ringkasan',
            'daftarPtKlien',
            'daftarPeriode',
            'daftarStatus',
            'filters',
        ));
    }
}
