<?php

declare(strict_types=1);

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Export laporan penggajian ke Excel.
 *
 * @see Req 10.6
 */
class LaporanPenggajianExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    public function __construct(
        private readonly Collection $dataSlipGaji,
        private readonly object $ringkasan,
    ) {}

    public function collection(): Collection
    {
        return $this->dataSlipGaji;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'Nama Karyawan',
            'PT Klien',
            'Periode',
            'Gaji Pokok',
            'Total Tunjangan',
            'Total Lembur',
            'Jam Lembur',
            'Total Potongan',
            'Gaji Bersih',
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

        return [
            $row->karyawan->nama_lengkap ?? '-',
            $row->karyawan->ptKlien->nama ?? '-',
            $labelPeriode,
            $row->gaji_pokok,
            $row->total_tunjangan,
            $row->total_lembur,
            $row->jam_lembur,
            $row->total_potongan,
            $row->gaji_bersih,
        ];
    }

    public function title(): string
    {
        return 'Laporan Penggajian';
    }
}
