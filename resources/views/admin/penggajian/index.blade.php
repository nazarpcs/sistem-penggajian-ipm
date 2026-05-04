@extends('layouts.app')

@section('title', 'Penggajian')
@section('page-title', 'Penggajian')

@section('content')
<div class="space-y-6" x-data="{ hitungModal: false, hitungLoading: false }">
    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="GET" action="{{ route('admin.penggajian.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
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
                <label for="periode_id" class="block text-sm font-medium text-slate-700 mb-1">Periode</label>
                <select id="periode_id" name="periode_id" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    <option value="">Semua Periode</option>
                    @foreach($periodes ?? [] as $p)
                        <option value="{{ $p->id }}" {{ request('periode_id') == $p->id ? 'selected' : '' }}>{{ sprintf('%02d/%d', $p->bulan, $p->tahun) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="bg-indigo-600 text-white rounded-lg px-4 py-2.5 text-sm font-medium hover:bg-indigo-700">Filter</button>
                <a href="{{ route('admin.penggajian.index') }}" class="border border-slate-200 text-slate-700 rounded-lg px-4 py-2.5 text-sm hover:bg-slate-50">Reset</a>
            </div>
            <div class="flex items-end justify-end">
                <button type="button" @click="hitungModal = true" class="bg-emerald-600 text-white rounded-lg px-4 py-2.5 text-sm font-medium hover:bg-emerald-700 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    Hitung Gaji
                </button>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Karyawan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">PT Klien</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Periode</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Gaji Pokok</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Tunjangan</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Lembur</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Potongan</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Gaji Bersih</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($slipGajis ?? [] as $slip)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ $slip->karyawan->nama_lengkap ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $slip->karyawan->ptKlien->nama ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $slip->periode ? sprintf('%02d/%d', $slip->periode->bulan, $slip->periode->tahun) : '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600 text-right">Rp {{ number_format($slip->gaji_pokok, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-emerald-600 text-right">+Rp {{ number_format($slip->total_tunjangan, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-blue-600 text-right">+Rp {{ number_format($slip->total_lembur, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-red-600 text-right">-Rp {{ number_format($slip->total_potongan, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-slate-900 text-right">Rp {{ number_format($slip->gaji_bersih, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('admin.penggajian.show', $slip->id) }}" class="p-2 rounded-lg hover:bg-slate-100 text-slate-500 hover:text-indigo-600" title="Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('admin.penggajian.pdf', $slip->id) }}" target="_blank" class="p-2 rounded-lg hover:bg-slate-100 text-slate-500 hover:text-red-600" title="Download PDF">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="px-4 py-12 text-center text-sm text-slate-500">Data slip gaji tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($slipGajis ?? collect(), 'links'))
        <div class="px-4 py-3 border-t border-slate-100">{{ $slipGajis->withQueryString()->links() }}</div>
        @endif
    </div>

    {{-- Hitung Gaji Modal --}}
    <div x-show="hitungModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" x-transition>
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="hitungModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full mx-4 p-6">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Hitung Gaji</h3>
            <form method="POST" action="{{ route('admin.penggajian.hitung') }}" @submit="hitungLoading = true">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="hitung_pt_klien_id" class="block text-sm font-medium text-slate-700 mb-1">PT Klien <span class="text-red-500">*</span></label>
                        <select id="hitung_pt_klien_id" name="pt_klien_id" required class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                            <option value="">Pilih PT Klien</option>
                            @foreach($ptKliens ?? [] as $pt)
                                <option value="{{ $pt->id }}">{{ $pt->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="hitung_periode_id" class="block text-sm font-medium text-slate-700 mb-1">Periode <span class="text-red-500">*</span></label>
                        <select id="hitung_periode_id" name="periode_id" required class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                            <option value="">Pilih Periode</option>
                            @foreach($periodes ?? [] as $p)
                                <option value="{{ $p->id }}">{{ sprintf('%02d/%d', $p->bulan, $p->tahun) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="hitungModal = false" class="border border-slate-200 text-slate-700 rounded-lg px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                    <button type="submit" :disabled="hitungLoading" class="bg-emerald-600 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-emerald-700 disabled:opacity-50 flex items-center gap-2">
                        <svg x-show="hitungLoading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <span x-text="hitungLoading ? 'Menghitung...' : 'Hitung Gaji'">Hitung Gaji</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
