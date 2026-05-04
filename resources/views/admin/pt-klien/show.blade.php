@extends('layouts.app')

@section('title', 'Detail PT Klien')
@section('page-title', 'Detail PT Klien')

@section('content')
<div class="max-w-4xl space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.pt-klien.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali
        </a>
        <div class="flex gap-2">
            <a href="{{ route('admin.pt-klien.konfigurasi-gaji.show', $ptKlien) }}" class="border border-slate-200 text-slate-700 rounded-lg px-4 py-2 text-sm hover:bg-slate-50 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Konfigurasi Gaji
            </a>
            <a href="{{ route('admin.pt-klien.edit', $ptKlien) }}" class="bg-indigo-600 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-indigo-700 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Informasi Perusahaan</h3>
            <dl class="space-y-3">
                <div class="flex justify-between"><dt class="text-sm text-slate-500">Nama</dt><dd class="text-sm font-medium text-slate-900">{{ $ptKlien->nama }}</dd></div>
                <div class="flex justify-between"><dt class="text-sm text-slate-500">PIC</dt><dd class="text-sm font-medium text-slate-900">{{ $ptKlien->nama_pic ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-sm text-slate-500">Telepon</dt><dd class="text-sm font-medium text-slate-900">{{ $ptKlien->telepon ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-sm text-slate-500">Email</dt><dd class="text-sm font-medium text-slate-900">{{ $ptKlien->email ?? '-' }}</dd></div>
                <div><dt class="text-sm text-slate-500 mb-1">Alamat</dt><dd class="text-sm text-slate-900">{{ $ptKlien->alamat ?? '-' }}</dd></div>
            </dl>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Data Kontrak</h3>
            <dl class="space-y-3">
                <div class="flex justify-between"><dt class="text-sm text-slate-500">No. Kontrak</dt><dd class="text-sm font-medium text-slate-900">{{ $ptKlien->nomor_kontrak ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-sm text-slate-500">Mulai</dt><dd class="text-sm font-medium text-slate-900">{{ $ptKlien->tgl_mulai?->format('d/m/Y') ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-sm text-slate-500">Berakhir</dt><dd class="text-sm font-medium text-slate-900">{{ $ptKlien->tgl_berakhir?->format('d/m/Y') ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-sm text-slate-500">Fee Jasa</dt><dd class="text-sm font-medium text-slate-900">Rp {{ number_format($ptKlien->fee_jasa ?? 0, 0, ',', '.') }}</dd></div>
            </dl>
        </div>
    </div>

    {{-- Daftar Karyawan --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="text-lg font-semibold text-slate-900">Daftar Karyawan ({{ $karyawans->count() }})</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Jabatan</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Gaji Pokok</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($karyawans as $k)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-sm font-medium text-slate-900">
                            <a href="{{ route('admin.karyawan.show', $k) }}" class="text-indigo-600 hover:text-indigo-700">{{ $k->nama_lengkap }}</a>
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $k->jabatan }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600 text-right">Rp {{ number_format($k->gaji_pokok, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center">@include('components.status-badge', ['status' => $k->status_aktif ? 'aktif' : 'alpha'])</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada karyawan terdaftar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
