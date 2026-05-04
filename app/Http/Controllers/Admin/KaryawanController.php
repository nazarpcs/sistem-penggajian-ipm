<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\KaryawanRequest;
use App\Models\PtKlien;
use App\Services\KaryawanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller CRUD karyawan untuk Admin.
 *
 * Mendelegasikan logika bisnis ke KaryawanService.
 * Menggunakan KaryawanRequest untuk validasi input.
 *
 * @see Req 3.1, 3.4, 3.5, 3.6
 */
class KaryawanController extends Controller
{
    public function __construct(
        private readonly KaryawanService $karyawanService,
    ) {}

    /**
     * Tampilkan daftar karyawan dengan filter.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['nama', 'pt_klien_id', 'jabatan', 'status_aktif']);
        $karyawans = $this->karyawanService->index($filters);
        $ptKliens = PtKlien::orderBy('nama')->get();

        return view('admin.karyawan.index', compact('karyawans', 'filters', 'ptKliens'));
    }

    /**
     * Tampilkan form tambah karyawan baru.
     */
    public function create(): View
    {
        $ptKliens = PtKlien::orderBy('nama')->get();

        return view('admin.karyawan.create', compact('ptKliens'));
    }

    /**
     * Simpan karyawan baru.
     */
    public function store(KaryawanRequest $request): RedirectResponse
    {
        $this->karyawanService->store($request->validated());

        return redirect()
            ->route('admin.karyawan.index')
            ->with('success', 'Karyawan berhasil ditambahkan. Kredensial login telah dikirim ke email karyawan.');
    }

    /**
     * Tampilkan detail karyawan.
     */
    public function show(int $karyawan): View
    {
        $data = $this->karyawanService->show($karyawan);

        return view('admin.karyawan.show', ['karyawan' => $data]);
    }

    /**
     * Tampilkan form edit karyawan.
     */
    public function edit(int $karyawan): View
    {
        $data = $this->karyawanService->show($karyawan);
        $ptKliens = PtKlien::orderBy('nama')->get();

        return view('admin.karyawan.edit', ['karyawan' => $data, 'ptKliens' => $ptKliens]);
    }

    /**
     * Update data karyawan.
     */
    public function update(KaryawanRequest $request, int $karyawan): RedirectResponse
    {
        $this->karyawanService->update($karyawan, $request->validated());

        return redirect()
            ->route('admin.karyawan.index')
            ->with('success', 'Data karyawan berhasil diperbarui.');
    }

    /**
     * Hapus karyawan dengan proteksi data terkait.
     *
     * Menerima parameter `force` dari request:
     * - Jika ada data terkait dan force bukan true → return JSON peringatan (untuk AJAX)
     *   atau redirect dengan peringatan (untuk form biasa)
     * - Jika force=true atau tidak ada data terkait → hapus dan redirect
     *
     * @see Req 3.5 (peringatan sebelum hapus karyawan dengan data terkait)
     */
    public function destroy(Request $request, int $karyawan): JsonResponse|RedirectResponse
    {
        $force = filter_var($request->input('force', false), FILTER_VALIDATE_BOOLEAN);

        $result = $this->karyawanService->destroy($karyawan, $force);

        // Jika tidak dihapus (ada data terkait, butuh konfirmasi)
        if (!$result['deleted']) {
            // AJAX request → return JSON response
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'deleted' => false,
                    'warning' => $result['warning'],
                    'has_related_data' => $result['has_related_data'],
                    'confirm_url' => route('admin.karyawan.destroy', $karyawan),
                    'message' => 'Konfirmasi diperlukan untuk menghapus karyawan ini.',
                ], 422);
            }

            // Regular request → redirect back dengan peringatan
            return redirect()
                ->back()
                ->with('warning', $result['warning'])
                ->with('confirm_delete_id', $karyawan);
        }

        // Berhasil dihapus
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'deleted' => true,
                'message' => 'Karyawan berhasil dihapus.',
            ]);
        }

        return redirect()
            ->route('admin.karyawan.index')
            ->with('success', 'Karyawan berhasil dihapus.');
    }

    /**
     * Cek data terkait sebelum hapus (untuk konfirmasi via AJAX).
     *
     * @see Req 3.5
     */
    public function checkDelete(int $karyawan): JsonResponse
    {
        $related = $this->karyawanService->checkRelatedData($karyawan);

        $hasRelated = $related['has_absensi'] || $related['has_slip_gaji'];
        $parts = [];
        if ($related['has_absensi']) {
            $parts[] = 'data absensi';
        }
        if ($related['has_slip_gaji']) {
            $parts[] = 'slip gaji';
        }

        return response()->json([
            'has_absensi' => $related['has_absensi'],
            'has_slip_gaji' => $related['has_slip_gaji'],
            'has_related_data' => $hasRelated,
            'nama_lengkap' => $related['nama_lengkap'],
            'warning' => $hasRelated
                ? 'Karyawan "' . $related['nama_lengkap'] . '" memiliki ' . implode(' dan ', $parts) . ' yang terkait.'
                : null,
        ]);
    }
}
