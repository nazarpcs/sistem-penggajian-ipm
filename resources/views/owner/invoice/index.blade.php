@extends('layouts.app')

@section('title', 'Invoice Approval')
@section('page-title', 'Invoice Approval')

@section('content')
<div class="space-y-6">
    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h2 class="text-lg font-semibold text-slate-900">Invoice Menunggu Approval</h2>
            <p class="text-sm text-slate-500 mt-1">Review dan setujui atau tolak invoice berikut</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">No. Invoice</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">PT Klien</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Periode</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Total Tagihan</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($invoices ?? [] as $inv)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ $inv->nomor_invoice }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $inv->ptKlien->nama ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $inv->periodePenggajian ? sprintf('%02d/%d', $inv->periodePenggajian->bulan, $inv->periodePenggajian->tahun) : '-' }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-slate-900 text-right">Rp {{ number_format($inv->total_tagihan, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center">@include('components.status-badge', ['status' => $inv->status])</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $inv->created_at->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('owner.invoice.show', $inv->id) }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">Review</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-sm text-slate-500">Tidak ada invoice yang memerlukan approval.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($invoices ?? collect(), 'links'))
        <div class="px-4 py-3 border-t border-slate-100">{{ $invoices->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection
