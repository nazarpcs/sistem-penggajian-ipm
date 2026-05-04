{{-- Template PDF Laporan Invoice --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Invoice</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 16px; }
        .header p { margin: 2px 0; font-size: 10px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #ddd; padding: 5px 8px; text-align: left; }
        th { background-color: #f5f5f5; font-size: 10px; text-transform: uppercase; }
        td { font-size: 10px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .section-title { font-size: 12px; font-weight: bold; margin: 15px 0 8px; }
        .ringkasan td { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>PT INDAH PERMATA MANDIRI</h1>
        <p>Laporan Invoice</p>
        <p>Dicetak: {{ $tanggalCetak }}</p>
    </div>

    <p class="section-title">Ringkasan</p>
    <table class="ringkasan">
        <tr>
            <td>Jumlah Invoice</td>
            <td class="text-right">{{ $ringkasan->jumlah_invoice }}</td>
            <td>Total Subtotal Gaji</td>
            <td class="text-right">Rp {{ number_format($ringkasan->total_subtotal_gaji, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Total Fee Jasa</td>
            <td class="text-right">Rp {{ number_format($ringkasan->total_fee_jasa, 0, ',', '.') }}</td>
            <td>Total Tagihan</td>
            <td class="text-right">Rp {{ number_format($ringkasan->total_tagihan, 0, ',', '.') }}</td>
        </tr>
    </table>

    <p class="section-title">Daftar Invoice</p>
    <table>
        <thead>
            <tr>
                <th>No. Invoice</th>
                <th>PT Klien</th>
                <th>Periode</th>
                <th class="text-right">Subtotal Gaji</th>
                <th class="text-right">Fee Jasa</th>
                <th class="text-right">Total Tagihan</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dataInvoice as $inv)
            <tr>
                <td>{{ $inv->nomor_invoice }}</td>
                <td>{{ $inv->ptKlien->nama ?? '-' }}</td>
                <td>
                    @if($inv->periodePenggajian)
                        {{ sprintf('%02d/%d', $inv->periodePenggajian->bulan, $inv->periodePenggajian->tahun) }}
                    @else
                        -
                    @endif
                </td>
                <td class="text-right">Rp {{ number_format($inv->subtotal_gaji, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($inv->fee_jasa, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($inv->total_tagihan, 0, ',', '.') }}</td>
                <td class="text-center">
                    @php
                        $labelStatus = match($inv->status) {
                            'menunggu_approval' => 'Menunggu Approval',
                            'disetujui' => 'Disetujui',
                            'ditolak' => 'Ditolak',
                            default => $inv->status,
                        };
                    @endphp
                    {{ $labelStatus }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
