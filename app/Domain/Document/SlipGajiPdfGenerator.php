<?php

declare(strict_types=1);

namespace App\Domain\Document;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

/**
 * Generator PDF untuk slip gaji karyawan.
 *
 * Menggunakan Barryvdh DomPDF dengan template Blade.
 * Return path file PDF yang disimpan di storage.
 *
 * @see GeneratorDokumenInterface::buatSlipGajiPdf()
 * @see Req 8.4 (unduh slip gaji PDF)
 */
class SlipGajiPdfGenerator
{
    /**
     * Buat slip gaji PDF untuk satu karyawan.
     *
     * @param mixed $slipGaji Model SlipGaji dengan relasi karyawan, ptKlien, periodePenggajian, komponenSlipGaji
     * @return string Path absolut file PDF yang dihasilkan
     */
    public function generate(mixed $slipGaji): string
    {
        $slipGaji->loadMissing(['karyawan.ptKlien', 'periodePenggajian', 'komponenSlipGaji']);

        $tunjangan = $slipGaji->komponenSlipGaji
            ->where('tipe', 'tunjangan');

        $potongan = $slipGaji->komponenSlipGaji
            ->where('tipe', 'potongan');

        $lembur = $slipGaji->komponenSlipGaji
            ->where('tipe', 'lembur');

        $data = [
            'slipGaji' => $slipGaji,
            'karyawan' => $slipGaji->karyawan,
            'ptKlien' => $slipGaji->karyawan->ptKlien,
            'periode' => $slipGaji->periodePenggajian,
            'tunjangan' => $tunjangan,
            'potongan' => $potongan,
            'lembur' => $lembur,
        ];

        $pdf = Pdf::loadView('pdf.slip-gaji', $data);
        $pdf->setPaper('A4', 'portrait');

        $filename = 'slip-gaji-' . $slipGaji->karyawan->nik . '-' . $slipGaji->periodePenggajian->tahun . '-' . str_pad((string) $slipGaji->periodePenggajian->bulan, 2, '0', STR_PAD_LEFT) . '.pdf';
        $directory = 'slip-gaji';

        if (!Storage::disk('local')->exists($directory)) {
            Storage::disk('local')->makeDirectory($directory);
        }

        $path = storage_path("app/{$directory}/{$filename}");
        file_put_contents($path, $pdf->output());

        return $path;
    }
}
