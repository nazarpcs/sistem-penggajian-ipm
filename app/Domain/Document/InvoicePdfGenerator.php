<?php

declare(strict_types=1);

namespace App\Domain\Document;

use App\Models\SlipGaji;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

/**
 * Generator PDF untuk invoice PT Klien.
 *
 * Menggunakan Barryvdh DomPDF dengan template Blade.
 * Return path file PDF yang disimpan di storage.
 *
 * @see GeneratorDokumenInterface::buatInvoicePdf()
 * @see Req 9.8 (unduh invoice PDF)
 */
class InvoicePdfGenerator
{
    /**
     * Buat invoice PDF untuk satu PT Klien.
     *
     * @param mixed $invoice Model Invoice dengan relasi ptKlien, periodePenggajian
     * @return string Path absolut file PDF yang dihasilkan
     */
    public function generate(mixed $invoice): string
    {
        $invoice->loadMissing(['ptKlien', 'periodePenggajian']);

        // Ambil rincian gaji per karyawan untuk periode dan PT Klien ini
        $slipGajiList = SlipGaji::with(['karyawan'])
            ->where('periode_id', $invoice->periode_id)
            ->whereHas('karyawan', function ($q) use ($invoice) {
                $q->where('pt_klien_id', $invoice->pt_klien_id);
            })
            ->get();

        $data = [
            'invoice' => $invoice,
            'ptKlien' => $invoice->ptKlien,
            'periode' => $invoice->periodePenggajian,
            'slipGajiList' => $slipGajiList,
        ];

        $pdf = Pdf::loadView('pdf.invoice', $data);
        $pdf->setPaper('A4', 'portrait');

        $filename = 'invoice-' . $invoice->nomor_invoice . '.pdf';
        $directory = 'invoice';

        if (!Storage::disk('local')->exists($directory)) {
            Storage::disk('local')->makeDirectory($directory);
        }

        $path = storage_path("app/{$directory}/{$filename}");
        file_put_contents($path, $pdf->output());

        return $path;
    }
}
