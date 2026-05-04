@extends('layouts.app')

@section('title', 'Review Invoice')
@section('page-title', 'Review Invoice')

@section('content')
<div class="max-w-4xl space-y-6" x-data="{ rejectModal: false, rejectLoading: false, approveLoading: false }">
    <div class="flex items-center justify-between">
        <a href="{{ route('owner.invoice.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali
        </a>
    </div>

    {{-- Invoice Detail --}}
    <div class="bg-white rounded-xl shadow-sm p-6 space-y-6">
        <div class="flex items-start justify-between border-b border-slate-100 pb-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">INVOICE</h2>
                <p class="text-lg font-semibold text-indigo-600 mt-1">{{ $invoice->nomor_invoice }}</p>
            </div>
            <div class="text-right">
                @include('components.status-badge', ['status' => $invoice->status])
                <p class="text-sm text-slate-500 mt-2">{{ $invoice->tanggal_pembuatan ? \Carbon\Carbon::parse($invoice->tanggal_pembuatan)->format('d/m/Y') : $invoice->created_at->format('d/m/Y') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Dari</p>
                <p class="text-sm font-semibold text-slate-900">PT Indah Permata Mandiri</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Kepada</p>
                <p class="text-sm font-semibold text-slate-900">{{ $invoice->ptKlien->nama ?? '-' }}</p>
                <p class="text-sm text-slate-600">PIC: {{ $invoice->ptKlien->nama_pic ?? '-' }}</p>
            </div>
        </div>

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

        {{-- Approval Actions --}}
        @if($invoice->status === 'menunggu_approval')
        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
            <button @click="rejectModal = true" class="bg-red-600 text-white rounded-lg px-6 py-2.5 text-sm font-medium hover:bg-red-700 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Tolak
            </button>
            <form method="POST" action="{{ route('owner.invoice.approve', $invoice->id) }}" @submit="approveLoading = true">
                @csrf
                <button type="submit" :disabled="approveLoading" class="bg-emerald-600 text-white rounded-lg px-6 py-2.5 text-sm font-medium hover:bg-emerald-700 disabled:opacity-50 flex items-center gap-2">
                    <svg x-show="approveLoading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    <svg x-show="!approveLoading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span x-text="approveLoading ? 'Memproses...' : 'Setujui'">Setujui</span>
                </button>
            </form>
        </div>
        @elseif($invoice->status === 'disetujui')
        <div class="bg-emerald-50 rounded-lg p-4">
            <p class="text-sm text-emerald-700"><span class="font-semibold">Disetujui oleh:</span> {{ $invoice->approvedBy->name ?? '-' }} pada {{ $invoice->approved_at ? \Carbon\Carbon::parse($invoice->approved_at)->format('d/m/Y H:i') : '-' }}</p>
        </div>
        @elseif($invoice->status === 'ditolak')
        <div class="bg-red-50 rounded-lg p-4">
            <p class="text-sm text-red-700"><span class="font-semibold">Ditolak oleh:</span> {{ $invoice->rejectedBy->name ?? '-' }}</p>
            <p class="text-sm text-red-700 mt-1"><span class="font-semibold">Alasan:</span> {{ $invoice->alasan_penolakan }}</p>
        </div>
        @endif
    </div>

    {{-- Reject Modal --}}
    <div x-show="rejectModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" x-transition>
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="rejectModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full mx-4 p-6">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Tolak Invoice</h3>
            <form method="POST" action="{{ route('owner.invoice.reject', $invoice->id) }}" @submit="rejectLoading = true">
                @csrf
                <div class="mb-4">
                    <label for="alasan_penolakan" class="block text-sm font-medium text-slate-700 mb-1">Alasan Penolakan <span class="text-red-500">*</span></label>
                    <textarea id="alasan_penolakan" name="alasan_penolakan" rows="4" required
                              class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                              placeholder="Jelaskan alasan penolakan invoice ini..."></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" @click="rejectModal = false" class="border border-slate-200 text-slate-700 rounded-lg px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                    <button type="submit" :disabled="rejectLoading" class="bg-red-600 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-red-700 disabled:opacity-50 flex items-center gap-2">
                        <svg x-show="rejectLoading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <span x-text="rejectLoading ? 'Memproses...' : 'Tolak Invoice'">Tolak Invoice</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
