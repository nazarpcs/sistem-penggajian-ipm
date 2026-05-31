<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Document\GeneratorDokumenInterface;
use App\Http\Controllers\Controller;
use App\Services\PenggajianService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Controller penggajian — hitung gaji, list slip, detail, download PDF.
 *
 * @see Req 7.1, 7.4, 7.5, 8.1, 8.2, 8.6
 */
class PenggajianController extends Controller
{
    public function __construct(
        private readonly PenggajianService $penggajianService,
        private readonly GeneratorDokumenInterface $generatorDokumen,
    ) {}

    /**
     * Jalankan perhitungan gaji untuk periode dan PT Klien tertentu.
     */
    public function hitung(Request $request): RedirectResponse
    {
        $request->validate([
            'periode_id' => 'required|integer|exists:periode_penggajian,id',
            'pt_klien_id' => 'required|integer|exists:pt_klien,id',
        ]);

        $result = $this->penggajianService->hitungGaji(
            (int) $request->input('periode_id'),
            (int) $request->input('pt_klien_id'),
        );

        if (!$result['success']) {
            return redirect()->back()
                ->withInput()
                ->with('error', $result['message']);
        }

        $flashData = ['success' => $result['message']];
        if (!empty($result['warnings'])) {
            $flashData['warnings'] = $result['warnings'];
        }

        return redirect()->route('admin.penggajian.index')
            ->with($flashData);
    }

    /**
     * List slip gaji dengan filter.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['pt_klien_id', 'periode_id', 'karyawan_id', 'nama']);
        $slipGaji = $this->penggajianService->listSlipGaji($filters);
        $ptKliens = \App\Models\PtKlien::orderBy('nama')->get();
        $periodes = \App\Models\PeriodePenggajian::orderBy('tahun', 'desc')->orderBy('bulan', 'desc')->get();

        return view('admin.penggajian.index', compact('slipGaji', 'filters', 'ptKliens', 'periodes'));
    }

    /**
     * Detail slip gaji dengan komponen rincian.
     */
    public function show(int $id): View|RedirectResponse
    {
        $result = $this->penggajianService->detailSlipGaji($id);

        if (!$result['success']) {
            return redirect()->route('admin.penggajian.index')
                ->with('error', $result['error']);
        }

        $slipGaji = $result['data'];

        return view('admin.penggajian.show', compact('slipGaji'));
    }

    /**
     * Unduh PDF slip gaji.
     */
    public function downloadPdf(int $id): BinaryFileResponse|RedirectResponse
    {
        $result = $this->penggajianService->detailSlipGaji($id);

        if (!$result['success']) {
            abort(404, 'Slip gaji tidak ditemukan.');
        }

        $slipGaji = $result['data'];
        $path = $this->generatorDokumen->buatSlipGajiPdf($slipGaji);

        $filename = 'slip-gaji-' . $slipGaji->karyawan->nik . '.pdf';

        return response()->download($path, $filename, [
            'Content-Type' => 'application/pdf',
        ])->deleteFileAfterSend(true);
    }
}
