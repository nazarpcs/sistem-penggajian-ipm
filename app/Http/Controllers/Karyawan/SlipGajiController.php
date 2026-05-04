<?php

declare(strict_types=1);

namespace App\Http\Controllers\Karyawan;

use App\Domain\Document\GeneratorDokumenInterface;
use App\Http\Controllers\Controller;
use App\Models\SlipGaji;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Controller slip gaji karyawan (self-service).
 *
 * Karyawan hanya dapat melihat dan mengunduh slip gaji miliknya sendiri.
 * Isolasi data via Policy: SlipGajiPolicy::view() dan ::download().
 *
 * @see Req 8.3, 8.4, 8.5 (akses slip gaji karyawan)
 * @see Property 6: Isolasi Data Karyawan
 */
class SlipGajiController extends Controller
{
    public function __construct(
        private readonly GeneratorDokumenInterface $generatorDokumen,
    ) {}

    /**
     * List slip gaji milik karyawan yang sedang login.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $karyawan = $user->karyawan;

        if (!$karyawan) {
            abort(404, 'Data karyawan tidak ditemukan.');
        }

        $slipGaji = SlipGaji::where('karyawan_id', $karyawan->id)
            ->with('periodePenggajian')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('karyawan.slip-gaji.index', compact('slipGaji'));
    }

    /**
     * Unduh PDF slip gaji milik karyawan.
     *
     * @see Req 8.4, 8.5
     */
    public function downloadPdf(Request $request, int $id): BinaryFileResponse
    {
        $slipGaji = SlipGaji::with(['karyawan.ptKlien', 'periodePenggajian', 'komponenSlipGaji'])
            ->findOrFail($id);

        // Policy check: karyawan hanya bisa download slip miliknya
        $this->authorize('download', $slipGaji);

        $path = $this->generatorDokumen->buatSlipGajiPdf($slipGaji);

        $filename = 'slip-gaji-' . $slipGaji->periodePenggajian->tahun . '-' . str_pad((string) $slipGaji->periodePenggajian->bulan, 2, '0', STR_PAD_LEFT) . '.pdf';

        return response()->download($path, $filename, [
            'Content-Type' => 'application/pdf',
        ])->deleteFileAfterSend(true);
    }
}
