@extends('layouts.app')

@section('title', 'Data Karyawan')
@section('page-title', 'Data Karyawan')

@section('content')
<div class="space-y-6" x-data="{ deleteModal: false, deleteUrl: '', deleteName: '' }">
    {{-- Filter & Actions --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="GET" action="{{ route('admin.karyawan.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-slate-700 mb-1">Cari Nama</label>
                <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Cari karyawan..."
                       class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
            </div>
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
                <label for="jabatan" class="block text-sm font-medium text-slate-700 mb-1">Jabatan</label>
                <input type="text" id="jabatan" name="jabatan" value="{{ request('jabatan') }}" placeholder="Filter jabatan..."
                       class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                <select id="status" name="status" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    <option value="">Semua Status</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Tidak Aktif</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="bg-indigo-600 text-white rounded-lg px-4 py-2.5 text-sm font-medium hover:bg-indigo-700 transition-colors">Filter</button>
                <a href="{{ route('admin.karyawan.index') }}" class="border border-slate-200 text-slate-700 rounded-lg px-4 py-2.5 text-sm hover:bg-slate-50 transition-colors">Reset</a>
            </div>
        </form>
    </div>

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-500">Total: {{ $karyawans->total() ?? 0 }} karyawan</p>
        <a href="{{ route('admin.karyawan.create') }}" class="bg-indigo-600 text-white rounded-lg px-4 py-2.5 text-sm font-medium hover:bg-indigo-700 transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Karyawan
        </a>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">NIK</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">PT Klien</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Jabatan</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Gaji Pokok</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($karyawans ?? [] as $k)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ $k->nama_lengkap }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $k->nik }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $k->ptKlien->nama ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $k->jabatan }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600 text-right">Rp {{ number_format($k->gaji_pokok, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center">
                            @include('components.status-badge', ['status' => $k->status_aktif ? 'aktif' : 'alpha'])
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('admin.karyawan.show', $k) }}" class="p-2 rounded-lg hover:bg-slate-100 text-slate-500 hover:text-indigo-600" title="Lihat">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('admin.karyawan.edit', $k) }}" class="p-2 rounded-lg hover:bg-slate-100 text-slate-500 hover:text-amber-600" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <button @click="deleteUrl = '{{ route('admin.karyawan.destroy', $k) }}'; deleteName = '{{ $k->nama_lengkap }}'; deleteModal = true"
                                        class="p-2 rounded-lg hover:bg-slate-100 text-slate-500 hover:text-red-600" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-sm text-slate-500">
                            <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Data karyawan tidak ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($karyawans ?? collect(), 'links'))
        <div class="px-4 py-3 border-t border-slate-100">{{ $karyawans->withQueryString()->links() }}</div>
        @endif
    </div>

    {{-- Delete Modal --}}
    <div x-show="deleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" x-transition>
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="deleteModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full mx-4 p-6" @click.away="deleteModal = false">
            <h3 class="text-lg font-semibold text-slate-900 mb-2">Konfirmasi Hapus</h3>
            <p class="text-sm text-slate-600 mb-6">Apakah Anda yakin ingin menghapus karyawan <span class="font-semibold" x-text="deleteName"></span>?</p>
            <div class="flex justify-end gap-3">
                <button @click="deleteModal = false" class="border border-slate-200 text-slate-700 rounded-lg px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                <form :action="deleteUrl" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-600 text-white rounded-lg px-4 py-2 text-sm hover:bg-red-700">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
