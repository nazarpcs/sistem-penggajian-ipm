@extends('layouts.app')

@section('title', 'Data Absensi')
@section('page-title', 'Data Absensi')

@section('content')
<div class="space-y-6" x-data="{ showForm: false }">
    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="GET" action="{{ route('admin.absensi.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <label for="karyawan_id" class="block text-sm font-medium text-slate-700 mb-1">Karyawan</label>
                <select id="karyawan_id" name="karyawan_id" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    <option value="">Semua Karyawan</option>
                    @foreach($karyawans ?? [] as $k)
                        <option value="{{ $k->id }}" {{ request('karyawan_id') == $k->id ? 'selected' : '' }}>{{ $k->nama_lengkap }}</option>
                    @endforeach
                </select>
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
                <label for="tanggal" class="block text-sm font-medium text-slate-700 mb-1">Tanggal</label>
                <input type="date" id="tanggal" name="tanggal" value="{{ request('tanggal') }}"
                       class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
            </div>
            <div>
                <label for="status_kehadiran" class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                <select id="status_kehadiran" name="status_kehadiran" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    <option value="">Semua</option>
                    <option value="Hadir" {{ request('status_kehadiran') == 'Hadir' ? 'selected' : '' }}>Hadir</option>
                    <option value="Izin" {{ request('status_kehadiran') == 'Izin' ? 'selected' : '' }}>Izin</option>
                    <option value="Sakit" {{ request('status_kehadiran') == 'Sakit' ? 'selected' : '' }}>Sakit</option>
                    <option value="Alpha" {{ request('status_kehadiran') == 'Alpha' ? 'selected' : '' }}>Alpha</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="bg-indigo-600 text-white rounded-lg px-4 py-2.5 text-sm font-medium hover:bg-indigo-700">Filter</button>
                <a href="{{ route('admin.absensi.index') }}" class="border border-slate-200 text-slate-700 rounded-lg px-4 py-2.5 text-sm hover:bg-slate-50">Reset</a>
            </div>
        </form>
    </div>

    {{-- Actions --}}
    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-500">Total: {{ $absensis->total() ?? 0 }} data</p>
        <div class="flex gap-2">
            <a href="{{ route('admin.absensi.rekap') }}" class="border border-slate-200 text-slate-700 rounded-lg px-4 py-2.5 text-sm hover:bg-slate-50 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Rekap
            </a>
            <a href="{{ route('admin.absensi.import', [], false) }}" class="border border-slate-200 text-slate-700 rounded-lg px-4 py-2.5 text-sm hover:bg-slate-50 flex items-center gap-2"
               onclick="event.preventDefault(); document.getElementById('importSection').scrollIntoView({behavior:'smooth'})">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Import Excel
            </a>
            <button @click="showForm = !showForm" class="bg-indigo-600 text-white rounded-lg px-4 py-2.5 text-sm font-medium hover:bg-indigo-700 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Input Manual
            </button>
        </div>
    </div>

    {{-- Manual Input Form --}}
    <div x-show="showForm" x-cloak x-transition class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">Input Absensi Manual</h3>
        <form method="POST" action="{{ route('admin.absensi.store') }}">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label for="form_karyawan_id" class="block text-sm font-medium text-slate-700 mb-1">Karyawan <span class="text-red-500">*</span></label>
                    <select id="form_karyawan_id" name="karyawan_id" required class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        <option value="">Pilih Karyawan</option>
                        @foreach($karyawans ?? [] as $k)
                            <option value="{{ $k->id }}">{{ $k->nama_lengkap }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="form_tanggal" class="block text-sm font-medium text-slate-700 mb-1">Tanggal <span class="text-red-500">*</span></label>
                    <input type="date" id="form_tanggal" name="tanggal" required class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                </div>
                <div>
                    <label for="form_status" class="block text-sm font-medium text-slate-700 mb-1">Status <span class="text-red-500">*</span></label>
                    <select id="form_status" name="status_kehadiran" required class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        <option value="Hadir">Hadir</option>
                        <option value="Izin">Izin</option>
                        <option value="Sakit">Sakit</option>
                        <option value="Alpha">Alpha</option>
                    </select>
                </div>
                <div>
                    <label for="jam_masuk" class="block text-sm font-medium text-slate-700 mb-1">Jam Masuk</label>
                    <input type="time" id="jam_masuk" name="jam_masuk" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                </div>
                <div>
                    <label for="jam_keluar" class="block text-sm font-medium text-slate-700 mb-1">Jam Keluar</label>
                    <input type="time" id="jam_keluar" name="jam_keluar" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                </div>
                <div>
                    <label for="keterangan" class="block text-sm font-medium text-slate-700 mb-1">Keterangan</label>
                    <input type="text" id="keterangan" name="keterangan" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-4">
                <button @click="showForm = false" type="button" class="border border-slate-200 text-slate-700 rounded-lg px-4 py-2.5 text-sm hover:bg-slate-50">Batal</button>
                <button type="submit" class="bg-indigo-600 text-white rounded-lg px-4 py-2.5 text-sm font-medium hover:bg-indigo-700">Simpan</button>
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
                        <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ $a->karyawan->nama_lengkap ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ \Carbon\Carbon::parse($a->tanggal)->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-center">@include('components.status-badge', ['status' => strtolower($a->status_kehadiran)])</td>
                        <td class="px-4 py-3 text-sm text-slate-600 text-center">{{ $a->jam_masuk ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600 text-center">{{ $a->jam_keluar ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600 text-center">{{ $a->jam_lembur ? number_format($a->jam_lembur, 1) . ' jam' : '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $a->keterangan ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-sm text-slate-500">Data absensi tidak ditemukan.</td></tr>
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
