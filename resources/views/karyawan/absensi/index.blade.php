@extends('layouts.app')

@section('title', 'Riwayat Absensi')
@section('page-title', 'Riwayat Absensi Saya')

@section('content')
<div class="space-y-6">
    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="GET" action="{{ route('karyawan.absensi.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label for="bulan" class="block text-sm font-medium text-slate-700 mb-1">Bulan</label>
                <select id="bulan" name="bulan" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ request('bulan', date('n')) == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label for="tahun" class="block text-sm font-medium text-slate-700 mb-1">Tahun</label>
                <select id="tahun" name="tahun" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                        <option value="{{ $y }}" {{ request('tahun', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-indigo-600 text-white rounded-lg px-4 py-2.5 text-sm font-medium hover:bg-indigo-700">Tampilkan</button>
            </div>
        </form>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @include('components.stat-card', ['title' => 'Hadir', 'value' => $summary['hadir'] ?? 0, 'color' => 'emerald', 'icon' => '<svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'])
        @include('components.stat-card', ['title' => 'Izin', 'value' => $summary['izin'] ?? 0, 'color' => 'blue', 'icon' => '<svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'])
        @include('components.stat-card', ['title' => 'Sakit', 'value' => $summary['sakit'] ?? 0, 'color' => 'blue', 'icon' => '<svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>'])
        @include('components.stat-card', ['title' => 'Alpha', 'value' => $summary['alpha'] ?? 0, 'color' => 'red', 'icon' => '<svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'])
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Jam Masuk</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Jam Keluar</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Lembur</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($absensis ?? [] as $a)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-sm text-slate-900">{{ \Carbon\Carbon::parse($a->tanggal)->translatedFormat('l, d M Y') }}</td>
                        <td class="px-4 py-3 text-center">@include('components.status-badge', ['status' => strtolower($a->status_kehadiran)])</td>
                        <td class="px-4 py-3 text-sm text-slate-600 text-center">{{ $a->jam_masuk ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600 text-center">{{ $a->jam_keluar ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600 text-center">{{ $a->jam_lembur ? number_format($a->jam_lembur, 1) . ' jam' : '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $a->keterangan ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-sm text-slate-500">Belum ada data absensi untuk periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($absensis ?? collect(), 'links'))
        <div class="px-4 py-3 border-t border-slate-100">{{ $absensis->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection
