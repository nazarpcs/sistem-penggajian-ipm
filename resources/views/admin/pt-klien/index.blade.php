@extends('layouts.app')

@section('title', 'Data PT Klien')
@section('page-title', 'Data PT Klien')

@section('content')
<div class="space-y-6">
    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="GET" action="{{ route('admin.pt-klien.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-slate-700 mb-1">Cari</label>
                <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Nama perusahaan..."
                       class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="bg-indigo-600 text-white rounded-lg px-4 py-2.5 text-sm font-medium hover:bg-indigo-700">Filter</button>
                <a href="{{ route('admin.pt-klien.index') }}" class="border border-slate-200 text-slate-700 rounded-lg px-4 py-2.5 text-sm hover:bg-slate-50">Reset</a>
            </div>
            <div class="flex items-end justify-end">
                <a href="{{ route('admin.pt-klien.create') }}" class="bg-indigo-600 text-white rounded-lg px-4 py-2.5 text-sm font-medium hover:bg-indigo-700 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah PT Klien
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
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Nama Perusahaan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">PIC</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">No. Kontrak</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Kontrak Berakhir</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Fee Jasa</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Karyawan</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($ptKliens ?? [] as $pt)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ $pt->nama }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $pt->nama_pic ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $pt->nomor_kontrak ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">
                            @if($pt->tgl_berakhir)
                                <span class="{{ $pt->tgl_berakhir->diffInDays(now()) <= 30 && $pt->tgl_berakhir->isFuture() ? 'text-amber-600 font-medium' : '' }}">
                                    {{ $pt->tgl_berakhir->format('d/m/Y') }}
                                </span>
                            @else - @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600 text-right">Rp {{ number_format($pt->fee_jasa ?? 0, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600 text-center">{{ $pt->karyawan_count ?? 0 }}</td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('admin.pt-klien.show', $pt) }}" class="p-2 rounded-lg hover:bg-slate-100 text-slate-500 hover:text-indigo-600" title="Lihat">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('admin.pt-klien.edit', $pt) }}" class="p-2 rounded-lg hover:bg-slate-100 text-slate-500 hover:text-amber-600" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <a href="{{ route('admin.pt-klien.konfigurasi-gaji.show', $pt) }}" class="p-2 rounded-lg hover:bg-slate-100 text-slate-500 hover:text-emerald-600" title="Konfigurasi Gaji">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-sm text-slate-500">Data PT Klien tidak ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($ptKliens ?? collect(), 'links'))
        <div class="px-4 py-3 border-t border-slate-100">{{ $ptKliens->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection
