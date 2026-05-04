<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Slip Gaji - {{ $karyawan->nama_lengkap }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #333; padding: 30px; }
        .header { text-align: center; border-bottom: 3px solid #2c3e50; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { font-size: 18px; color: #2c3e50; margin-bottom: 3px; }
        .header p { font-size: 10px; color: #666; }
        .title { text-align: center; font-size: 14px; font-weight: bold; color: #2c3e50; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 1px; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 3px 5px; font-size: 11px; }
        .info-table .label { font-weight: bold; width: 150px; color: #555; }
        .section-title { font-size: 12px; font-weight: bold; color: #2c3e50; border-bottom: 1px solid #ddd; padding-bottom: 5px; margin: 15px 0 10px 0; }
        .detail-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .detail-table th, .detail-table td { border: 1px solid #ddd; padding: 6px 10px; font-size: 11px; }
        .detail-table th { background-color: #f5f5f5; text-align: left; font-weight: bold; color: #2c3e50; }
        .detail-table .amount { text-align: right; }
        .total-row { background-color: #2c3e50; color: #fff; font-weight: bold; }
        .total-row td { border-color: #2c3e50; }
        .footer { margin-top: 30px; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>PT Indah Permata Mandiri</h1>
        <p>Jasa Outsourcing &amp; Pengelolaan SDM</p>
    </div>

    <div class="title">Slip Gaji Karyawan</div>

    <table class="info-table">
        <tr>
            <td class="label">Nama Karyawan</td>
            <td>: {{ $karyawan->nama_lengkap }}</td>
            <td class="label">Periode</td>
            <td>: {{ str_pad((string) $periode->bulan, 2, '0', STR_PAD_LEFT) }}/{{ $periode->tahun }}</td>
        </tr>
        <tr>
            <td class="label">NIK</td>
            <td>: {{ $karyawan->nik }}</td>
            <td class="label">PT Klien</td>
            <td>: {{ $ptKlien->nama }}</td>
        </tr>
        <tr>
            <td class="label">Jabatan</td>
            <td>: {{ $karyawan->jabatan }}</td>
            <td class="label">Tanggal Cetak</td>
            <td>: {{ now()->format('d/m/Y') }}</td>
        </tr>
    </table>

    <div class="section-title">Rincian Pendapatan</div>
    <table class="detail-table">
        <thead>
            <tr>
                <th style="width: 10%;">No</th>
                <th>Komponen</th>
                <th style="width: 30%;" class="amount">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>Gaji Pokok</td>
                <td class="amount">{{ number_format((float) $slipGaji->gaji_pokok, 0, ',', '.') }}</td>
            </tr>
            @php $no = 2; @endphp
            @foreach($tunjangan as $item)
            <tr>
                <td>{{ $no++ }}</td>
                <td>{{ $item->nama_komponen }}</td>
                <td class="amount">{{ number_format((float) $item->nilai, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            @foreach($lembur as $item)
            <tr>
                <td>{{ $no++ }}</td>
                <td>{{ $item->nama_komponen }} ({{ $slipGaji->jam_lembur }} jam)</td>
                <td class="amount">{{ number_format((float) $item->nilai, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($potongan->count() > 0)
    <div class="section-title">Rincian Potongan</div>
    <table class="detail-table">
        <thead>
            <tr>
                <th style="width: 10%;">No</th>
                <th>Komponen</th>
                <th style="width: 30%;" class="amount">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($potongan as $idx => $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->nama_komponen }}</td>
                <td class="amount">{{ number_format((float) $item->nilai, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <table class="detail-table">
        <tr class="total-row">
            <td colspan="2" style="text-align: right; padding-right: 15px;">GAJI BERSIH</td>
            <td class="amount" style="width: 30%;">Rp {{ number_format((float) $slipGaji->gaji_bersih, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="footer">
        <p>Dokumen ini dicetak secara otomatis oleh Sistem Penggajian PT Indah Permata Mandiri.</p>
        <p>Slip gaji ini sah tanpa tanda tangan.</p>
    </div>
</body>
</html>
