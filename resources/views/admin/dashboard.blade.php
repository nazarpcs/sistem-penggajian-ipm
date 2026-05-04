@extends('layouts.app')

@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">
    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @include('components.stat-card', [
            'title' => 'Total Karyawan Aktif',
            'value' => number_format($totalKaryawanAktif),
            'color' => 'indigo',
            'icon' => '<svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'
        ])
        @include('components.stat-card', [
            'title' => 'Total PT Klien Aktif',
            'value' => number_format($totalPtKlienAktif),
            'color' => 'emerald',
            'icon' => '<svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>'
        ])
        @include('components.stat-card', [
            'title' => 'Penggajian Bulan Ini',
            'value' => 'Rp ' . number_format($ringkasanPenggajian, 0, ',', '.'),
            'color' => 'purple',
            'icon' => '<svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        ])
        @include('components.stat-card', [
            'title' => 'Invoice Pending',
            'value' => $invoicePending->count(),
            'color' => 'amber',
            'icon' => '<svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'
        ])
    </div>

    {{-- Kontrak Warning --}}
    @if($kontrakAkanBerakhir->isNotEmpty())
    <div class="bg-amber-50 border-l-4 border-amber-500 text-amber-700 p-4 rounded-lg" role="alert">
        <div class="flex items-center mb-2">
            <svg class="w-5 h-5 text-amber-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            <h3 class="text-sm font-semibold">Kontrak Akan Berakhir (30 Hari ke Depan)</h3>
        </div>
        <ul class="list-disc list-inside text-sm space-y-1">
            @foreach($kontrakAkanBerakhir as $kontrak)
                <li>{{ $kontrak->nama }} — berakhir {{ $kontrak->tgl_berakhir->translatedFormat('d F Y') }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Invoice Pending Table --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h2 class="text-lg font-semibold text-slate-900">Invoice Menunggu Approval</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">No. Invoice</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">PT Klien</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Periode</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Total Tagihan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($invoicePending as $inv)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ $inv->nomor_invoice }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $inv->ptKlien->nama ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">
                            {{ $inv->periodePenggajian ? sprintf('%02d/%d', $inv->periodePenggajian->bulan, $inv->periodePenggajian->tahun) : '-' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600 text-right">Rp {{ number_format($inv->total_tagihan, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $inv->created_at->format('d/m/Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">Tidak ada invoice menunggu approval.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
