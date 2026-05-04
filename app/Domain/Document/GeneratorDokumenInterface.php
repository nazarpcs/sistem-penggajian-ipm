<?php

namespace App\Domain\Document;

/**
 * Interface GeneratorDokumen
 *
 * Menghasilkan dokumen PDF dan Excel menggunakan template Blade.
 *
 * @see design.md — Komponen Inti: GeneratorDokumen
 */
interface GeneratorDokumenInterface
{
    /**
     * Buat slip gaji PDF untuk satu karyawan.
     *
     * @param mixed $slipGaji Model SlipGaji
     * @return string Path file PDF yang dihasilkan
     */
    public function buatSlipGajiPdf(mixed $slipGaji): string;

    /**
     * Buat invoice PDF untuk satu PT Klien.
     *
     * @param mixed $invoice Model Invoice
     * @return string Path file PDF yang dihasilkan
     */
    public function buatInvoicePdf(mixed $invoice): string;

    /**
     * Buat laporan dalam format Excel.
     *
     * @param string $tipe Tipe laporan (absensi, penggajian, invoice)
     * @param array<string, mixed> $filter Filter laporan
     * @return string Path file yang dihasilkan
     */
    public function buatLaporanExcel(string $tipe, array $filter): string;
}
