<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Document\GeneratorDokumenInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\InvoiceRequest;
use App\Models\Invoice;
use App\Models\PeriodePenggajian;
use App\Models\PtKlien;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Controller invoice admin — buat, list, detail, download PDF.
 *
 * @see Req 9.1-9.10
 * @see Property 15: Format dan Uniqueness Nomor Invoice
 * @see Property 16: Pencegahan Duplikasi Invoice
 */
class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
        private readonly GeneratorDokumenInterface $generatorDokumen,
    ) {}

    /**
     * List invoice dengan filter.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['pt_klien_id', 'periode_id', 'status']);
        $invoices = $this->invoiceService->listInvoice($filters);
        $ptKliens = PtKlien::orderBy('nama')->get();
        $periodes = PeriodePenggajian::orderBy('tahun', 'desc')->orderBy('bulan', 'desc')->get();

        return view('admin.invoice.index', compact('invoices', 'filters', 'ptKliens', 'periodes'));
    }

    /**
     * Buat invoice baru.
     */
    public function store(InvoiceRequest $request): RedirectResponse
    {
        $result = $this->invoiceService->buatInvoice(
            (int) $request->validated()['pt_klien_id'],
            (int) $request->validated()['periode_id'],
        );

        if (!$result['success']) {
            return redirect()->back()
                ->withInput()
                ->with('error', $result['message']);
        }

        return redirect()->route('admin.invoice.index')
            ->with('success', $result['message']);
    }

    /**
     * Detail invoice.
     */
    public function show(int $id): View|RedirectResponse
    {
        $result = $this->invoiceService->detailInvoice($id);

        if (!$result['success']) {
            return redirect()->route('admin.invoice.index')
                ->with('error', $result['error']);
        }

        $invoice = $result['data'];

        return view('admin.invoice.show', compact('invoice'));
    }

    /**
     * Unduh PDF invoice (hanya jika status 'disetujui').
     *
     * @see Req 9.8
     */
    public function downloadPdf(int $id): BinaryFileResponse|RedirectResponse
    {
        $invoice = Invoice::find($id);

        if (!$invoice) {
            abort(404, 'Invoice tidak ditemukan.');
        }

        $this->authorize('download', $invoice);

        $path = $this->generatorDokumen->buatInvoicePdf($invoice);

        return response()->download($path, "invoice-{$invoice->nomor_invoice}.pdf", [
            'Content-Type' => 'application/pdf',
        ])->deleteFileAfterSend(true);
    }
}
