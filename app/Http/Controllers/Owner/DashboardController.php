<?php

declare(strict_types=1);

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\PeriodePenggajian;
use App\Models\SlipGaji;
use App\Traits\HasAuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Dashboard Pemilik PT — ringkasan pengeluaran dan invoice pending.
 *
 * Menampilkan total pengeluaran bulan ini, grafik tren 12 bulan terakhir
 * (data untuk Chart.js), dan daftar invoice yang memerlukan approval.
 *
 * @see Req 10.2, 10.7
 */
class DashboardController extends Controller
{
    use HasAuditLog;

    /**
     * Tampilkan dashboard pemilik PT.
     *
     * Data:
     * - Total pengeluaran gaji bulan ini
     * - Tren pengeluaran 12 bulan terakhir (untuk Chart.js line/bar chart)
     * - Daftar invoice menunggu approval
     */
    public function index(): View
    {
        // Invoice menunggu approval
        $invoicePending = Invoice::where('status', 'menunggu_approval')
            ->with(['ptKlien', 'periodePenggajian'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Tren pengeluaran 12 bulan terakhir via periode_penggajian
        $pengeluaranBulanan = SlipGaji::query()
            ->join('periode_penggajian', 'slip_gaji.periode_id', '=', 'periode_penggajian.id')
            ->select(
                'periode_penggajian.tahun',
                'periode_penggajian.bulan',
                DB::raw('SUM(slip_gaji.gaji_bersih) as total'),
            )
            ->where(DB::raw("CONCAT(periode_penggajian.tahun, '-', LPAD(periode_penggajian.bulan, 2, '0'))"), '>=', now()->subMonths(11)->format('Y-m'))
            ->groupBy('periode_penggajian.tahun', 'periode_penggajian.bulan')
            ->orderBy('periode_penggajian.tahun', 'asc')
            ->orderBy('periode_penggajian.bulan', 'asc')
            ->get();

        // Total pengeluaran bulan ini
        $periodeBulanIni = PeriodePenggajian::where('bulan', (int) now()->month)
            ->where('tahun', (int) now()->year)
            ->first();

        $totalPengeluaranBulanIni = 0;
        if ($periodeBulanIni) {
            $totalPengeluaranBulanIni = (float) SlipGaji::where('periode_id', $periodeBulanIni->id)
                ->sum('gaji_bersih');
        }

        // Format data untuk Chart.js
        $chartLabels = $pengeluaranBulanan->map(function ($item) {
            $namaBulan = [
                1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
                5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
                9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
            ];

            return ($namaBulan[$item->bulan] ?? '') . ' ' . $item->tahun;
        })->values();

        $chartData = $pengeluaranBulanan->pluck('total')->map(fn ($val) => (float) $val)->values();

        return view('owner.dashboard', compact(
            'invoicePending',
            'pengeluaranBulanan',
            'totalPengeluaranBulanIni',
            'chartLabels',
            'chartData',
        ));
    }
}
