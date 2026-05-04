<?php

declare(strict_types=1);

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller approval invoice oleh Pemilik PT.
 *
 * @see Req 9.4-9.7 (approval workflow)
 * @see Property 17: Invariant Audit Log
 */
class InvoiceApprovalController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
    ) {}

    /**
     * List invoice menunggu approval.
     */
    public function index(Request $request): View
    {
        $invoices = $this->invoiceService->listInvoice(
            array_merge($request->only(['pt_klien_id', 'periode_id']), ['status' => 'menunggu_approval']),
        );

        return view('owner.invoice.index', compact('invoices'));
    }

    /**
     * Detail invoice untuk review.
     */
    public function show(int $id): View
    {
        $invoice = Invoice::with(['ptKlien', 'periodePenggajian', 'approvedBy', 'rejectedBy'])->findOrFail($id);

        return view('owner.invoice.show', compact('invoice'));
    }

    /**
     * Approve invoice.
     *
     * @see Req 9.5
     */
    public function approve(int $id): RedirectResponse
    {
        $invoice = Invoice::findOrFail($id);
        $this->authorize('approve', $invoice);

        $result = $this->invoiceService->approveInvoice($id, (int) auth()->id());

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        return redirect()->route('owner.invoice.index')
            ->with('success', $result['message']);
    }

    /**
     * Reject invoice (alasan penolakan wajib).
     *
     * @see Req 9.6, 9.7
     */
    public function reject(Request $request, int $id): RedirectResponse
    {
        $invoice = Invoice::findOrFail($id);
        $this->authorize('reject', $invoice);

        $request->validate([
            'alasan_penolakan' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'alasan_penolakan.required' => 'Alasan penolakan wajib diisi.',
            'alasan_penolakan.min' => 'Alasan penolakan minimal 10 karakter.',
        ]);

        $result = $this->invoiceService->rejectInvoice(
            $id,
            (int) auth()->id(),
            $request->input('alasan_penolakan'),
        );

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        return redirect()->route('owner.invoice.index')
            ->with('success', $result['message']);
    }
}
