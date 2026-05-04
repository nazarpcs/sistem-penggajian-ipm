<?php

declare(strict_types=1);

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Export laporan absensi ke Excel.
 *
 * @see Req 10.6
 */
class LaporanAbsensiExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    public function __construct(
        private readonly Collection $dataAbsensi,
        private readonly Collection $rekapPerKaryawan,
    ) {}

    public function collection(): Collection
    {
        return $this->dataAbsensi;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'Tanggal',
            'Nama Karyawan',
            'PT Klien',
            'Status Kehadiran',
            'Jam Masuk',
            'Jam Keluar',
            'Jam Lembur',
            'Keterangan',
        ];
    }

    /**
     * @param mixed $row
     * @return array<int, mixed>
     */
    public function map(mixed $row): array
    {
        return [
            $row->tanggal->format('d/m/Y'),
            $row->karyawan->nama_lengkap ?? '-',
            $row->karyawan->ptKlien->nama ?? '-',
            $row->status_kehadiran,
            $row->jam_masuk ?? '-',
            $row->jam_keluar ?? '-',
            $row->jam_lembur,
            $row->keterangan ?? '-',
        ];
    }

    public function title(): string
    {
        return 'Laporan Absensi';
    }
}
