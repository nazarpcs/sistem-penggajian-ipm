@extends('layouts.app')

@section('title', 'Detail Invoice')
@section('page-title', 'Detail Invoice')

@section('content')
<div class="max-w-4xl space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.invoice.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali
        </a>
        @if($invoice->status === 'disetujui')
        <a href="{{ route('admin.invoice.pdf', $invoice->id) }}" target="_blank"
           class="bg-red-600 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-red-700 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Download PDF
        </a>
        @endif
    </div>

    {{-- Invoice Card --}}
    <div class="bg-white rounded-xl shadow-sm p-6 space-y-6">
        {{-- Header --}}
        <div class="flex items-start justify-between border-b border-slate-100 pb-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">INVOICE</h2>
                <p class="text-lg font-semibold text-indigo-600 mt-1">{{ $invoice->nomor_invoice }}</p>
            </div>
            <div class="text-right">
                @include('components.status-badge', ['status' => $invoice->status])
                <p class="text-sm text-slate-500 mt-2">Tanggal: {{ $invoice->tanggal_pembuatan ? \Carbon\Carbon::parse($invoice->tanggal_pembuatan)->format('d/m/Y') : $invoice->created_at->format('d/m/Y') }}</p>
            </div>
        </div>

        {{-- Info --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Dari</p>
                <p class="text-sm font-semibold text-slate-900">PT Indah Permata Mandiri</p>
                <p class="text-sm text-slate-600">Sistem Penggajian</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Kepada</p>
                <p class="text-sm font-semibold text-slate-900">{{ $invoice->ptKlien->nama ?? '-' }}</p>
                <p class="text-sm text-slate-600">{{ $invoice->ptKlien->alamat ?? '' }}</p>
                <p class="text-sm text-slate-600">PIC: {{ $invoice->ptKlien->nama_pic ?? '-' }}</p>
            </div>
        </div>

        {{-- Rincian --}}
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-3">Rincian Tagihan</p>
            <div class="bg-slate-50 rounded-lg overflow-hidden">
                <table class="min-w-full">
                    <tbody class="divide-y divide-slate-200">
                        <tr>
                            <td class="px-4 py-3 text-sm text-slate-600">Subtotal Gaji Karyawan</td>
                            <td class="px-4 py-3 text-sm font-medium text-slate-900 text-right">Rp {{ number_format($invoice->subtotal_gaji, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 text-sm text-slate-600">Fee Jasa PT IPM</td>
                            <td class="px-4 py-3 text-sm font-medium text-slate-900 text-right">Rp {{ number_format($invoice->fee_jasa, 0, ',', '.') }}</td>
                        </tr>
                        @if($invoice->pajak)
                        <tr>
                            <td class="px-4 py-3 text-sm text-slate-600">Pajak</td>
                            <td class="px-4 py-3 text-sm font-medium text-slate-900 text-right">Rp {{ number_format($invoice->pajak, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                    </tbody>
                    <tfoot>
                        <tr class="bg-indigo-50">
                            <td class="px-4 py-3 text-sm font-semibold text-slate-900">Total Tagihan</td>
                            <td class="px-4 py-3 text-lg font-bold text-indigo-600 text-right">Rp {{ number_format($invoice->total_tagihan, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Approval Info --}}
        @if($invoice->status === 'disetujui')
        <div class="bg-emerald-50 rounded-lg p-4">
            <p class="text-sm text-emerald-700"><span class="font-semibold">Disetujui oleh:</span> {{ $invoice->approvedBy->name ?? '-' }} pada {{ $invoice->approved_at ? \Carbon\Carbon::parse($invoice->approved_at)->format('d/m/Y H:i') : '-' }}</p>
        </div>
        @elseif($invoice->status === 'ditolak')
        <div class="bg-red-50 rounded-lg p-4">
            <p class="text-sm text-red-700"><span class="font-semibold">Ditolak oleh:</span> {{ $invoice->rejectedBy->name ?? '-' }} pada {{ $invoice->rejected_at ? \Carbon\Carbon::parse($invoice->rejected_at)->format('d/m/Y H:i') : '-' }}</p>
            <p class="text-sm text-red-700 mt-1"><span class="font-semibold">Alasan:</span> {{ $invoice->alasan_penolakan }}</p>
        </div>
        @endif
    </div>
</div>
@endsection
