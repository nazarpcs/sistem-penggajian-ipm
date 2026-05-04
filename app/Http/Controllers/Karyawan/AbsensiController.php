<?php

declare(strict_types=1);

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller riwayat absensi karyawan (self-service).
 *
 * Karyawan hanya dapat melihat riwayat absensi miliknya sendiri.
 *
 * @see Req 2.4 (akses karyawan: riwayat absensi pribadi)
 * @see Property 6: Isolasi Data Karyawan
 */
class AbsensiController extends Controller
{
    /**
     * Tampilkan riwayat absensi karyawan yang sedang login.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $karyawan = $user->karyawan;

        if (!$karyawan) {
            abort(404, 'Data karyawan tidak ditemukan.');
        }

        $query = Absensi::where('karyawan_id', $karyawan->id);

        if ($request->filled('bulan') && $request->filled('tahun')) {
            $query->whereMonth('tanggal', $request->input('bulan'))
                ->whereYear('tanggal', $request->input('tahun'));
        }

        $absensi = $query->orderBy('tanggal', 'desc')->paginate(15);

        return view('karyawan.absensi.index', compact('absensi'));
    }
}
