{{-- Template PDF Laporan Absensi --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Absensi</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 16px; }
        .header p { margin: 2px 0; font-size: 10px; color: #666; }
        .meta { margin-bottom: 15px; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #ddd; padding: 5px 8px; text-align: left; }
        th { background-color: #f5f5f5; font-size: 10px; text-transform: uppercase; }
        td { font-size: 10px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .section-title { font-size: 12px; font-weight: bold; margin: 15px 0 8px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>PT INDAH PERMATA MANDIRI</h1>
        <p>Laporan Absensi Karyawan</p>
        <p>Dicetak: {{ $tanggalCetak }}</p>
    </div>

    <div class="meta">
        @if(!empty($filters['tanggal_mulai']) || !empty($filters['tanggal_selesai']))
            <p>Periode: {{ $filters['tanggal_mulai'] ?? '-' }} s/d {{ $filters['tanggal_selesai'] ?? '-' }}</p>
        @endif
    </div>

    @if($rekapPerKaryawan->isNotEmpty())
    <p class="section-title">Rekap Per Karyawan</p>
    <table>
        <thead>
            <tr>
                <th>Karyawan</th>
                <th>PT Klien</th>
                <th class="text-center">Hadir</th>
                <th class="text-center">Izin</th>
                <th class="text-center">Sakit</th>
                <th class="text-center">Alpha</th>
                <th class="text-center">Jam Lembur</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rekapPerKaryawan as $rekap)
            <tr>
                <td>{{ $rekap->karyawan->nama_lengkap }}</td>
                <td>{{ $rekap->karyawan->ptKlien->nama ?? '-' }}</td>
                <td class="text-center">{{ $rekap->total_hadir }}</td>
                <td class="text-center">{{ $rekap->total_izin }}</td>
                <td class="text-center">{{ $rekap->total_sakit }}</td>
                <td class="text-center">{{ $rekap->total_alpha }}</td>
                <td class="text-center">{{ number_format($rekap->total_jam_lembur, 1) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <p class="section-title">Detail Absensi</p>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Karyawan</th>
                <th>Status</th>
                <th>Jam Masuk</th>
                <th>Jam Keluar</th>
                <th class="text-center">Lembur</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dataAbsensi as $absensi)
            <tr>
                <td>{{ $absensi->tanggal->format('d/m/Y') }}</td>
                <td>{{ $absensi->karyawan->nama_lengkap ?? '-' }}</td>
                <td>{{ $absensi->status_kehadiran }}</td>
                <td>{{ $absensi->jam_masuk ?? '-' }}</td>
                <td>{{ $absensi->jam_keluar ?? '-' }}</td>
                <td class="text-center">{{ $absensi->jam_lembur > 0 ? number_format((float) $absensi->jam_lembur, 1) : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
