<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $invoice->nomor_invoice }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: #333; padding: 30px; }
        .header { border-bottom: 3px solid #2c3e50; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { font-size: 18px; color: #2c3e50; }
        .header p { font-size: 10px; color: #666; }
        .invoice-title { text-align: center; font-size: 16px; font-weight: bold; color: #2c3e50; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 2px; }
        .meta-table { width: 100%; margin-bottom: 20px; }
        .meta-table td { padding: 3px 5px; font-size: 11px; vertical-align: top; }
        .meta-table .label { font-weight: bold; width: 140px; color: #555; }
        .section-title { font-size: 12px; font-weight: bold; color: #2c3e50; border-bottom: 1px solid #ddd; padding-bottom: 5px; margin: 15px 0 10px 0; }
        .detail-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .detail-table th, .detail-table td { border: 1px solid #ddd; padding: 5px 8px; font-size: 10px; }
        .detail-table th { background-color: #2c3e50; color: #fff; text-align: left; font-weight: bold; }
        .detail-table .amount { text-align: right; }
        .detail-table .center { text-align: center; }
        .summary-table { width: 50%; margin-left: auto; border-collapse: collapse; margin-bottom: 20px; }
        .summary-table td { padding: 6px 10px; font-size: 11px; border: 1px solid #ddd; }
        .summary-table .label { font-weight: bold; text-align: right; background-color: #f9f9f9; }
        .summary-table .amount { text-align: right; }
        .total-row { background-color: #2c3e50; color: #fff; font-weight: bold; }
        .total-row td { border-color: #2c3e50; }
        .footer { margin-top: 40px; font-size: 9px; color: #999; border-top: 1px solid #ddd; padding-top: 10px; }
        .signature { margin-top: 40px; width: 100%; }
        .signature td { width: 50%; text-align: center; font-size: 11px; vertical-align: top; padding-top: 10px; }
        .signature .line { border-top: 1px solid #333; width: 180px; margin: 60px auto 5px auto; }
    </style>
</head>
<body>
    <div class="header">
        <h1>PT Indah Permata Mandiri</h1>
        <p>Jasa Outsourcing &amp; Pengelolaan SDM</p>
    </div>

    <div class="invoice-title">Invoice</div>

    <table class="meta-table">
        <tr>
            <td class="label">Nomor Invoice</td>
            <td>: {{ $invoice->nomor_invoice }}</td>
            <td class="label">Tanggal</td>
            <td>: {{ \Carbon\Carbon::parse($invoice->tanggal_pembuatan)->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="label">Kepada</td>
            <td>: {{ $ptKlien->nama }}</td>
            <td class="label">Periode</td>
            <td>: {{ str_pad((string) $periode->bulan, 2, '0', STR_PAD_LEFT) }}/{{ $periode->tahun }}</td>
        </tr>
        <tr>
            <td class="label">Alamat</td>
            <td>: {{ $ptKlien->alamat }}</td>
            <td class="label">Status</td>
            <td>: {{ ucfirst(str_replace('_', ' ', $invoice->status)) }}</td>
        </tr>
        <tr>
            <td class="label">PIC</td>
            <td>: {{ $ptKlien->nama_pic }}</td>
            <td></td>
            <td></td>
        </tr>
    </table>

    <div class="section-title">Rincian Gaji Karyawan</div>
    <table class="detail-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="center">No</th>
                <th style="width: 25%;">Nama Karyawan</th>
                <th style="width: 15%;">Jabatan</th>
                <th style="width: 13%;" class="amount">Gaji Pokok</th>
                <th style="width: 13%;" class="amount">Tunjangan</th>
                <th style="width: 10%;" class="amount">Lembur</th>
                <th style="width: 10%;" class="amount">Potongan</th>
                <th style="width: 13%;" class="amount">Gaji Bersih</th>
            </tr>
        </thead>
        <tbody>
            @foreach($slipGajiList as $slip)
            <tr>
                <td class="center">{{ $loop->iteration }}</td>
                <td>{{ $slip->karyawan->nama_lengkap }}</td>
                <td>{{ $slip->karyawan->jabatan }}</td>
                <td class="amount">{{ number_format((float) $slip->gaji_pokok, 0, ',', '.') }}</td>
                <td class="amount">{{ number_format((float) $slip->total_tunjangan, 0, ',', '.') }}</td>
                <td class="amount">{{ number_format((float) $slip->total_lembur, 0, ',', '.') }}</td>
                <td class="amount">{{ number_format((float) $slip->total_potongan, 0, ',', '.') }}</td>
                <td class="amount">{{ number_format((float) $slip->gaji_bersih, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary-table">
        <tr>
            <td class="label">Subtotal Gaji</td>
            <td class="amount">Rp {{ number_format((float) $invoice->subtotal_gaji, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Fee Jasa PT IPM</td>
            <td class="amount">Rp {{ number_format((float) $invoice->fee_jasa, 0, ',', '.') }}</td>
        </tr>
        @if((float) $invoice->pajak > 0)
        <tr>
            <td class="label">Pajak</td>
            <td class="amount">Rp {{ number_format((float) $invoice->pajak, 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr class="total-row">
            <td class="label" style="background-color: #2c3e50; color: #fff;">TOTAL TAGIHAN</td>
            <td class="amount">Rp {{ number_format((float) $invoice->total_tagihan, 0, ',', '.') }}</td>
        </tr>
    </table>

    <table class="signature">
        <tr>
            <td>
                <p>Dibuat oleh,</p>
                <div class="line"></div>
                <p>PT Indah Permata Mandiri</p>
            </td>
            <td>
                <p>Disetujui oleh,</p>
                <div class="line"></div>
                <p>{{ $ptKlien->nama_pic }}</p>
            </td>
        </tr>
    </table>

    <div class="footer">
        <p>Dokumen ini dicetak secara otomatis oleh Sistem Penggajian PT Indah Permata Mandiri.</p>
    </div>
</body>
</html>
