<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AbsensiRequest;
use App\Models\Karyawan;
use App\Models\PeriodePenggajian;
use App\Models\PtKlien;
use App\Services\AbsensiService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Controller absensi — input manual, import Excel, rekap, kunci/buka kunci periode.
 *
 * Semua operasi didelegasikan ke AbsensiService.
 * Hanya Admin yang dapat mengakses (dijaga oleh middleware role:admin).
 *
 * @see Req 5.1-5.7, 6.1-6.7
 */
class AbsensiController extends Controller
{
    public function __construct(
        private readonly AbsensiService $absensiService,
    ) {
    }

    /**
     * List absensi dengan filter dan paginasi.
     */
    public function index(Request $request): View
    {
        $filters = $request->only([
            'karyawan_id',
            'pt_klien_id',
            'tanggal',
            'status_kehadiran',
        ]);

        $absensis = $this->absensiService->index($filters, 15);
        $karyawans = Karyawan::where('status_aktif', true)->orderBy('nama_lengkap')->get();
        $ptKliens = PtKlien::orderBy('nama')->get();

        return view('admin.absensi.index', compact('absensis', 'karyawans', 'ptKliens', 'filters'));
    }

    /**
     * Simpan absensi manual. Return 409 jika duplikat.
     */
    public function store(AbsensiRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->absensiService->store($request->validated());

        if ($request->expectsJson()) {
            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['error'],
                ], $result['code']);
            }
            return response()->json([
                'success' => true,
                'message' => 'Data absensi berhasil disimpan.',
                'data' => $result['data'],
            ], 201);
        }

        if (!$result['success']) {
            return redirect()->route('admin.absensi.index')
                ->with('error', $result['error']);
        }

        return redirect()->route('admin.absensi.index')
            ->with('success', 'Data absensi berhasil disimpan.');
    }

    /**
     * Update absensi.
     */
    public function update(AbsensiRequest $request, int $id): JsonResponse
    {
        $result = $this->absensiService->update($id, $request->validated());

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['error'],
            ], $result['code']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data absensi berhasil diperbarui.',
            'data' => $result['data'],
        ]);
    }

    /**
     * Upload file Excel absensi. Validasi sinkron, dispatch job jika valid.
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
        ], [
            'file.required' => 'File Excel wajib diupload.',
            'file.mimes' => 'Format file harus .xlsx atau .xls.',
            'file.max' => 'Ukuran file maksimal 10MB.',
        ]);

        $result = $this->absensiService->importExcel($request->file('file'));

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'errors' => $result['errors'] ?? [],
                'total_baris' => $result['total_baris'] ?? 0,
            ], $result['code']);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'total_baris' => $result['total_baris'],
        ]);
    }

    /**
     * Rekap absensi per periode dan PT Klien.
     */
    public function rekap(Request $request): View
    {
        $ptKliens = PtKlien::orderBy('nama')->get();
        $periodes = PeriodePenggajian::orderBy('tahun', 'desc')->orderBy('bulan', 'desc')->get();
        $filters = $request->only(['periode_id', 'pt_klien_id']);
        $rekap = null;
        $warnings = [];

        if ($request->filled('periode_id') && $request->filled('pt_klien_id')) {
            $result = $this->absensiService->rekap(
                (int) $request->input('periode_id'),
                (int) $request->input('pt_klien_id'),
            );
            $rekap = $result['rekap'];
            $warnings = $result['warnings'];
        }

        return view('admin.absensi.rekap', compact('ptKliens', 'periodes', 'filters', 'rekap', 'warnings'));
    }

    /**
     * Kunci periode absensi.
     */
    public function kunci(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'periode_id' => ['required', 'integer', 'exists:periode_penggajian,id'],
        ]);

        $result = $this->absensiService->kunciPeriode(
            (int) $request->input('periode_id'),
        );

        if ($request->expectsJson()) {
            return response()->json($result, $result['success'] ? 200 : ($result['code'] ?? 400));
        }

        return redirect()->route('admin.absensi.rekap', $request->only('periode_id', 'pt_klien_id'))
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Buka kunci periode absensi + alasan + audit log.
     */
    public function bukaKunci(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'periode_id' => ['required', 'integer', 'exists:periode_penggajian,id'],
            'alasan' => ['required', 'string', 'max:500'],
        ]);

        $result = $this->absensiService->bukaKunciPeriode(
            (int) $request->input('periode_id'),
            $request->input('alasan'),
        );

        if ($request->expectsJson()) {
            return response()->json($result, $result['success'] ? 200 : ($result['code'] ?? 400));
        }

        return redirect()->route('admin.absensi.rekap', $request->only('periode_id', 'pt_klien_id'))
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}
