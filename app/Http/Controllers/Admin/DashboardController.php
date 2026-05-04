<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Karyawan;
use App\Models\PeriodePenggajian;
use App\Models\PtKlien;
use App\Models\SlipGaji;
use App\Traits\HasAuditLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * Dashboard Admin — ringkasan operasional sistem penggajian.
 *
 * Menampilkan total karyawan aktif, PT Klien aktif, ringkasan
 * penggajian bulan berjalan, invoice pending, dan kontrak akan berakhir.
 *
 * @see Req 10.1
 */
class DashboardController extends Controller
{
    use HasAuditLog;

    /**
     * Tampilkan dashboard admin dengan ringkasan operasional.
     *
     * Data yang ditampilkan:
     * - Total karyawan aktif
     * - Total PT Klien aktif (kontrak masih berlaku)
     * - Ringkasan penggajian bulan berjalan (sum gaji_bersih)
     * - Daftar invoice menunggu approval
     * - Kontrak PT Klien yang akan berakhir (dari cache)
     */
    public function index(): View
    {
        $totalKaryawanAktif = Karyawan::where('status_aktif', true)->count();

        $totalPtKlienAktif = PtKlien::where('tgl_berakhir', '>=', now())->count();

        // Ringkasan penggajian bulan berjalan
        $periodeBulanIni = PeriodePenggajian::where('bulan', (int) now()->month)
            ->where('tahun', (int) now()->year)
            ->first();

        $ringkasanPenggajian = 0;
        if ($periodeBulanIni) {
            $ringkasanPenggajian = (float) SlipGaji::where('periode_id', $periodeBulanIni->id)
                ->sum('gaji_bersih');
        }

        // Invoice menunggu approval
        $invoicePending = Invoice::where('status', 'menunggu_approval')
            ->with(['ptKlien', 'periodePenggajian'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Kontrak akan berakhir (dari cache CheckKontrakKadaluarsaJob)
        $kontrakAkanBerakhir = Cache::get('kontrak_akan_berakhir', collect());

        return view('admin.dashboard', compact(
            'totalKaryawanAktif',
            'totalPtKlienAktif',
            'ringkasanPenggajian',
            'invoicePending',
            'kontrakAkanBerakhir',
        ));
    }
}
