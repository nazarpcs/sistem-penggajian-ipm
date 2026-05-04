<?php

declare(strict_types=1);

namespace App\Domain\Document;

/**
 * Facade GeneratorDokumen — mendelegasikan pembuatan dokumen ke generator spesifik.
 *
 * - Slip Gaji PDF → SlipGajiPdfGenerator
 * - Invoice PDF → InvoicePdfGenerator
 * - Laporan Excel → Maatwebsite Excel (stub, Task 15.3)
 *
 * @see GeneratorDokumenInterface
 * @see Req 8.4, 9.8, 10.6
 */
class GeneratorDokumen implements GeneratorDokumenInterface
{
    public function __construct(
        private readonly SlipGajiPdfGenerator $slipGajiPdfGenerator,
        private readonly InvoicePdfGenerator $invoicePdfGenerator,
    ) {}

    /**
     * Buat slip gaji PDF untuk satu karyawan.
     *
     * @param mixed $slipGaji Model SlipGaji
     * @return string Path file PDF yang dihasilkan
     */
    public function buatSlipGajiPdf(mixed $slipGaji): string
    {
        return $this->slipGajiPdfGenerator->generate($slipGaji);
    }

    /**
     * Buat invoice PDF untuk satu PT Klien.
     *
     * @param mixed $invoice Model Invoice
     * @return string Path file PDF yang dihasilkan
     */
    public function buatInvoicePdf(mixed $invoice): string
    {
        return $this->invoicePdfGenerator->generate($invoice);
    }

    /**
     * Buat laporan dalam format Excel.
     *
     * @param string $tipe Tipe laporan (absensi, penggajian, invoice)
     * @param array<string, mixed> $filter Filter laporan
     * @return string Path file yang dihasilkan
     */
    public function buatLaporanExcel(string $tipe, array $filter): string
    {
        // Implementasi lengkap di Task 15.3 menggunakan Maatwebsite Excel
        throw new \RuntimeException("Laporan Excel tipe '{$tipe}' belum diimplementasikan.");
    }
}
