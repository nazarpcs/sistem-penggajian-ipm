@extends('layouts.app')

@section('title', 'Detail Slip Gaji')
@section('page-title', 'Detail Slip Gaji')

@section('content')
<div class="max-w-3xl space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.penggajian.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali
        </a>
        <a href="{{ route('admin.penggajian.pdf', $slip->id) }}" target="_blank"
           class="bg-red-600 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-red-700 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Download PDF
        </a>
    </div>

    {{-- Slip Gaji Card --}}
    <div class="bg-white rounded-xl shadow-sm p-6 space-y-6">
        {{-- Header --}}
        <div class="border-b border-slate-100 pb-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">Slip Gaji</h2>
                    <p class="text-sm text-slate-500">Periode: {{ $slip->periode ? sprintf('%02d/%d', $slip->periode->bulan, $slip->periode->tahun) : '-' }}</p>
                </div>
                @include('components.status-badge', ['status' => $slip->status ?? 'final'])
            </div>
        </div>

        {{-- Info Karyawan --}}
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div><span class="text-slate-500">Nama:</span> <span class="font-medium text-slate-900">{{ $slip->karyawan->nama_lengkap ?? '-' }}</span></div>
            <div><span class="text-slate-500">PT Klien:</span> <span class="font-medium text-slate-900">{{ $slip->karyawan->ptKlien->nama ?? '-' }}</span></div>
            <div><span class="text-slate-500">Jabatan:</span> <span class="font-medium text-slate-900">{{ $slip->karyawan->jabatan ?? '-' }}</span></div>
            <div><span class="text-slate-500">NIK:</span> <span class="font-medium text-slate-900">{{ $slip->karyawan->nik ?? '-' }}</span></div>
        </div>

        {{-- Rincian Gaji --}}
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-slate-900">Rincian Gaji</h3>

            {{-- Pendapatan --}}
            <div class="bg-slate-50 rounded-lg p-4 space-y-2">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Pendapatan</p>
                <div class="flex justify-between text-sm"><span class="text-slate-600">Gaji Pokok</span><span class="font-medium">Rp {{ number_format($slip->gaji_pokok, 0, ',', '.') }}</span></div>
                @foreach($slip->komponens->where('tipe', 'tunjangan') ?? [] as $komp)
                <div class="flex justify-between text-sm"><span class="text-slate-600">{{ $komp->nama_komponen }}</span><span class="font-medium text-emerald-600">+Rp {{ number_format($komp->nilai, 0, ',', '.') }}</span></div>
                @endforeach
                <div class="flex justify-between text-sm"><span class="text-slate-600">Lembur ({{ number_format($slip->jam_lembur ?? 0, 1) }} jam)</span><span class="font-medium text-blue-600">+Rp {{ number_format($slip->total_lembur, 0, ',', '.') }}</span></div>
            </div>

            {{-- Potongan --}}
            <div class="bg-slate-50 rounded-lg p-4 space-y-2">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Potongan</p>
                @forelse($slip->komponens->where('tipe', 'potongan') ?? [] as $komp)
                <div class="flex justify-between text-sm"><span class="text-slate-600">{{ $komp->nama_komponen }}</span><span class="font-medium text-red-600">-Rp {{ number_format($komp->nilai, 0, ',', '.') }}</span></div>
                @empty
                <div class="flex justify-between text-sm"><span class="text-slate-600">Potongan Alpha</span><span class="font-medium text-red-600">-Rp {{ number_format($slip->total_potongan, 0, ',', '.') }}</span></div>
                @endforelse
            </div>

            {{-- Total --}}
            <div class="border-t-2 border-slate-200 pt-4">
                <div class="flex justify-between items-center">
                    <span class="text-lg font-semibold text-slate-900">Gaji Bersih</span>
                    <span class="text-2xl font-bold text-indigo-600">Rp {{ number_format($slip->gaji_bersih, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
