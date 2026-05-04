@extends('layouts.app')

@section('title', 'Rekap Absensi')
@section('page-title', 'Rekap Absensi')

@section('content')
<div class="space-y-6" x-data="{ kunciModal: false, bukaKunciModal: false }">
    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="GET" action="{{ route('admin.absensi.rekap') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label for="pt_klien_id" class="block text-sm font-medium text-slate-700 mb-1">PT Klien</label>
                <select id="pt_klien_id" name="pt_klien_id" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    <option value="">Semua PT Klien</option>
                    @foreach($ptKliens ?? [] as $pt)
                        <option value="{{ $pt->id }}" {{ request('pt_klien_id') == $pt->id ? 'selected' : '' }}>{{ $pt->nama }}</option>
                    @endforeach
                </select>
            </div>
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
            <div class="flex items-end gap-2">
                <button type="submit" class="bg-indigo-600 text-white rounded-lg px-4 py-2.5 text-sm font-medium hover:bg-indigo-700">Tampilkan</button>
            </div>
        </form>
    </div>

    {{-- Periode Status & Lock --}}
    @if(isset($periode))
    <div class="flex items-center justify-between bg-white rounded-xl shadow-sm p-4">
        <div class="flex items-center gap-3">
            <span class="text-sm text-slate-600">Status Periode:</span>
            @include('components.status-badge', ['status' => $periode->status ?? 'aktif'])
        </div>
        <div class="flex gap-2">
            @if(($periode->status ?? 'aktif') === 'aktif')
            <button @click="kunciModal = true" class="bg-amber-500 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-amber-600 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                Kunci Periode
            </button>
            @else
            <button @click="bukaKunciModal = true" class="bg-emerald-500 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-emerald-600 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                Buka Kunci
            </button>
            @endif
        </div>
    </div>
    @endif

    {{-- Rekap Table --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Karyawan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">PT Klien</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Hadir</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Izin</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Sakit</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Alpha</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Jam Lembur</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rekap ?? [] as $r)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ $r->nama_lengkap ?? $r->karyawan->nama_lengkap ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $r->pt_klien_nama ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-center"><span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 font-medium text-xs">{{ $r->total_hadir ?? 0 }}</span></td>
                        <td class="px-4 py-3 text-sm text-center"><span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-700 font-medium text-xs">{{ $r->total_izin ?? 0 }}</span></td>
                        <td class="px-4 py-3 text-sm text-center"><span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-700 font-medium text-xs">{{ $r->total_sakit ?? 0 }}</span></td>
                        <td class="px-4 py-3 text-sm text-center"><span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-100 text-red-700 font-medium text-xs">{{ $r->total_alpha ?? 0 }}</span></td>
                        <td class="px-4 py-3 text-sm text-slate-600 text-center">{{ number_format($r->total_jam_lembur ?? 0, 1) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-sm text-slate-500">Data rekap tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Kunci Modal --}}
    <div x-show="kunciModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" x-transition>
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="kunciModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full mx-4 p-6">
            <h3 class="text-lg font-semibold text-slate-900 mb-2">Kunci Periode Absensi</h3>
            <p class="text-sm text-slate-600 mb-4">Setelah dikunci, data absensi pada periode ini tidak dapat diubah. Lanjutkan?</p>
            <form method="POST" action="{{ route('admin.absensi.kunci') }}">
                @csrf
                <input type="hidden" name="bulan" value="{{ request('bulan', date('n')) }}">
                <input type="hidden" name="tahun" value="{{ request('tahun', date('Y')) }}">
                <input type="hidden" name="pt_klien_id" value="{{ request('pt_klien_id') }}">
                <div class="flex justify-end gap-3">
                    <button type="button" @click="kunciModal = false" class="border border-slate-200 text-slate-700 rounded-lg px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                    <button type="submit" class="bg-amber-500 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-amber-600">Kunci</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Buka Kunci Modal --}}
    <div x-show="bukaKunciModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" x-transition>
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="bukaKunciModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full mx-4 p-6">
            <h3 class="text-lg font-semibold text-slate-900 mb-2">Buka Kunci Periode</h3>
            <form method="POST" action="{{ route('admin.absensi.buka-kunci') }}">
                @csrf
                <input type="hidden" name="bulan" value="{{ request('bulan', date('n')) }}">
                <input type="hidden" name="tahun" value="{{ request('tahun', date('Y')) }}">
                <input type="hidden" name="pt_klien_id" value="{{ request('pt_klien_id') }}">
                <div class="mb-4">
                    <label for="alasan" class="block text-sm font-medium text-slate-700 mb-1">Alasan Pembukaan Kunci <span class="text-red-500">*</span></label>
                    <textarea id="alasan" name="alasan" rows="3" required class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none" placeholder="Masukkan alasan..."></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" @click="bukaKunciModal = false" class="border border-slate-200 text-slate-700 rounded-lg px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                    <button type="submit" class="bg-emerald-500 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-emerald-600">Buka Kunci</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
