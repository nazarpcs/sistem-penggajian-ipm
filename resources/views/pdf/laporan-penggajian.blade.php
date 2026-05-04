{{-- Template PDF Laporan Penggajian --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Penggajian</title>
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
        .section-title { font-size: 12px; font-weight: bold; margin: 15px 0 8px; }
        .ringkasan { margin-bottom: 15px; }
        .ringkasan td { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>PT INDAH PERMATA MANDIRI</h1>
        <p>Laporan Penggajian Karyawan</p>
        <p>Dicetak: {{ $tanggalCetak }}</p>
    </div>

    <p class="section-title">Ringkasan</p>
    <table class="ringkasan">
        <tr>
            <td>Jumlah Karyawan</td>
            <td class="text-right">{{ $ringkasan->jumlah_karyawan }}</td>
            <td>Total Gaji Pokok</td>
            <td class="text-right">Rp {{ number_format($ringkasan->total_gaji_pokok, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Total Tunjangan</td>
            <td class="text-right">Rp {{ number_format($ringkasan->total_tunjangan, 0, ',', '.') }}</td>
            <td>Total Lembur</td>
            <td class="text-right">Rp {{ number_format($ringkasan->total_lembur, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Total Potongan</td>
            <td class="text-right">Rp {{ number_format($ringkasan->total_potongan, 0, ',', '.') }}</td>
            <td>Total Gaji Bersih</td>
            <td class="text-right">Rp {{ number_format($ringkasan->total_gaji_bersih, 0, ',', '.') }}</td>
        </tr>
    </table>

    <p class="section-title">Rincian Penggajian</p>
    <table>
        <thead>
            <tr>
                <th>Karyawan</th>
                <th>PT Klien</th>
                <th class="text-right">Gaji Pokok</th>
                <th class="text-right">Tunjangan</th>
                <th class="text-right">Lembur</th>
                <th class="text-right">Potongan</th>
                <th class="text-right">Gaji Bersih</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dataSlipGaji as $slip)
            <tr>
                <td>{{ $slip->karyawan->nama_lengkap ?? '-' }}</td>
                <td>{{ $slip->karyawan->ptKlien->nama ?? '-' }}</td>
                <td class="text-right">Rp {{ number_format($slip->gaji_pokok, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($slip->total_tunjangan, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($slip->total_lembur, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($slip->total_potongan, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($slip->gaji_bersih, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
