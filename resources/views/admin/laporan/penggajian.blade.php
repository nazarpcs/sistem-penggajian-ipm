@extends('layouts.app')

@section('title', 'Laporan Penggajian')
@section('page-title', 'Laporan Penggajian')

@section('content')
<div class="space-y-6">
    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="GET" action="{{ request()->url() }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
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
                <button type="submit" class="bg-indigo-600 text-white rounded-lg px-4 py-2.5 text-sm font-medium hover:bg-indigo-700">Tampilkan</button>
            </div>
            <div class="flex items-end gap-2">
                <a href="{{ request()->url() }}?{{ http_build_query(array_merge(request()->query(), ['export' => 'pdf'])) }}" class="border border-red-200 text-red-600 rounded-lg px-4 py-2.5 text-sm hover:bg-red-50 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                    PDF
                </a>
                <a href="{{ request()->url() }}?{{ http_build_query(array_merge(request()->query(), ['export' => 'excel'])) }}" class="border border-emerald-200 text-emerald-600 rounded-lg px-4 py-2.5 text-sm hover:bg-emerald-50 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                    Excel
                </a>
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
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($data ?? [] as $slip)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ $slip->karyawan->nama_lengkap ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $slip->karyawan->ptKlien->nama ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $slip->periode ? sprintf('%02d/%d', $slip->periode->bulan, $slip->periode->tahun) : '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600 text-right">Rp {{ number_format($slip->gaji_pokok, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-emerald-600 text-right">+Rp {{ number_format($slip->total_tunjangan, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-blue-600 text-right">+Rp {{ number_format($slip->total_lembur, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-red-600 text-right">-Rp {{ number_format($slip->total_potongan, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-slate-900 text-right">Rp {{ number_format($slip->gaji_bersih, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-4 py-12 text-center text-sm text-slate-500">Data tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
