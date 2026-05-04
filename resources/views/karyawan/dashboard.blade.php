@extends('layouts.app')

@section('title', 'Dashboard Karyawan')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">
    {{-- Welcome --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-xl font-bold">
                {{ strtoupper(substr($karyawan->nama_lengkap ?? 'U', 0, 1)) }}
            </div>
            <div>
                <h2 class="text-xl font-semibold text-slate-900">Selamat datang, {{ $karyawan->nama_lengkap ?? 'Karyawan' }}</h2>
                <p class="text-sm text-slate-500">{{ $karyawan->jabatan ?? '' }} — {{ $karyawan->ptKlien->nama ?? '' }}</p>
            </div>
        </div>
    </div>

    {{-- Absensi Summary --}}
    <div>
        <h3 class="text-lg font-semibold text-slate-900 mb-3">Absensi Bulan Ini</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @include('components.stat-card', ['title' => 'Hadir', 'value' => $absensiSummary['hadir'] ?? 0, 'color' => 'emerald', 'icon' => '<svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'])
            @include('components.stat-card', ['title' => 'Izin', 'value' => $absensiSummary['izin'] ?? 0, 'color' => 'blue', 'icon' => '<svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'])
            @include('components.stat-card', ['title' => 'Sakit', 'value' => $absensiSummary['sakit'] ?? 0, 'color' => 'blue', 'icon' => '<svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>'])
            @include('components.stat-card', ['title' => 'Alpha', 'value' => $absensiSummary['alpha'] ?? 0, 'color' => 'red', 'icon' => '<svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'])
        </div>
    </div>

    {{-- Latest Slip Gaji --}}
    @if(isset($latestSlip) && $latestSlip)
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-slate-900">Slip Gaji Terakhir</h3>
            <a href="{{ route('karyawan.slip-gaji.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">Lihat Semua</a>
        </div>
        <div class="bg-slate-50 rounded-lg p-4 space-y-3">
            <div class="flex justify-between text-sm"><span class="text-slate-500">Periode</span><span class="font-medium">{{ $latestSlip->periode ? sprintf('%02d/%d', $latestSlip->periode->bulan, $latestSlip->periode->tahun) : '-' }}</span></div>
            <div class="flex justify-between text-sm"><span class="text-slate-500">Gaji Pokok</span><span class="font-medium">Rp {{ number_format($latestSlip->gaji_pokok, 0, ',', '.') }}</span></div>
            <div class="flex justify-between text-sm"><span class="text-slate-500">Tunjangan</span><span class="font-medium text-emerald-600">+Rp {{ number_format($latestSlip->total_tunjangan, 0, ',', '.') }}</span></div>
            <div class="flex justify-between text-sm"><span class="text-slate-500">Lembur</span><span class="font-medium text-blue-600">+Rp {{ number_format($latestSlip->total_lembur, 0, ',', '.') }}</span></div>
            <div class="flex justify-between text-sm"><span class="text-slate-500">Potongan</span><span class="font-medium text-red-600">-Rp {{ number_format($latestSlip->total_potongan, 0, ',', '.') }}</span></div>
            <div class="border-t border-slate-200 pt-3 flex justify-between">
                <span class="font-semibold text-slate-900">Gaji Bersih</span>
                <span class="text-lg font-bold text-indigo-600">Rp {{ number_format($latestSlip->gaji_bersih, 0, ',', '.') }}</span>
            </div>
        </div>
        <div class="mt-4">
            <a href="{{ route('karyawan.slip-gaji.pdf', $latestSlip->id) }}" target="_blank"
               class="inline-flex items-center gap-2 text-sm text-red-600 hover:text-red-700 font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Download PDF
            </a>
        </div>
    </div>
    @endif
</div>
@endsection
