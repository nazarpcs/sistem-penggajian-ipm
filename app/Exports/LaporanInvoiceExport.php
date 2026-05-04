<?php

declare(strict_types=1);

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Export laporan invoice ke Excel.
 *
 * @see Req 10.6
 */
class LaporanInvoiceExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    public function __construct(
        private readonly Collection $dataInvoice,
        private readonly object $ringkasan,
    ) {}

    public function collection(): Collection
    {
        return $this->dataInvoice;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'Nomor Invoice',
            'PT Klien',
            'Periode',
            'Tanggal Pembuatan',
            'Subtotal Gaji',
            'Fee Jasa',
            'Pajak',
            'Total Tagihan',
            'Status',
        ];
    }

    /**
     * @param mixed $row
     * @return array<int, mixed>
     */
    public function map(mixed $row): array
    {
        $periode = $row->periodePenggajian;
        $labelPeriode = $periode
            ? sprintf('%02d/%d', $periode->bulan, $periode->tahun)
            : '-';

        $statusLabel = match ($row->status) {
            'menunggu_approval' => 'Menunggu Approval',
            'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
            default => $row->status,
        };

        return [
            $row->nomor_invoice,
            $row->ptKlien->nama ?? '-',
            $labelPeriode,
            $row->tanggal_pembuatan->format('d/m/Y'),
            $row->subtotal_gaji,
            $row->fee_jasa,
            $row->pajak,
            $row->total_tagihan,
            $statusLabel,
        ];
    }

    public function title(): string
    {
        return 'Laporan Invoice';
    }
}
