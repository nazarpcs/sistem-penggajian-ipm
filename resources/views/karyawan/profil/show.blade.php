@extends('layouts.app')

@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')

@section('content')
<div class="max-w-3xl space-y-6" x-data="{ editing: false, loading: false }">
    {{-- Profile Card --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-2xl font-bold">
                {{ strtoupper(substr($karyawan->nama_lengkap, 0, 1)) }}
            </div>
            <div>
                <h2 class="text-xl font-semibold text-slate-900">{{ $karyawan->nama_lengkap }}</h2>
                <p class="text-sm text-slate-500">{{ $karyawan->jabatan }} — {{ $karyawan->ptKlien->nama ?? '-' }}</p>
            </div>
        </div>

        {{-- View Mode --}}
        <div x-show="!editing">
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><dt class="text-sm text-slate-500">NIK</dt><dd class="text-sm font-medium text-slate-900 mt-0.5">{{ $karyawan->nik }}</dd></div>
                <div><dt class="text-sm text-slate-500">Email</dt><dd class="text-sm font-medium text-slate-900 mt-0.5">{{ $karyawan->user->email ?? '-' }}</dd></div>
                <div><dt class="text-sm text-slate-500">Tanggal Lahir</dt><dd class="text-sm font-medium text-slate-900 mt-0.5">{{ $karyawan->tanggal_lahir?->format('d/m/Y') ?? '-' }}</dd></div>
                <div><dt class="text-sm text-slate-500">Telepon</dt><dd class="text-sm font-medium text-slate-900 mt-0.5">{{ $karyawan->telepon ?? '-' }}</dd></div>
                <div class="md:col-span-2"><dt class="text-sm text-slate-500">Alamat</dt><dd class="text-sm font-medium text-slate-900 mt-0.5">{{ $karyawan->alamat ?? '-' }}</dd></div>
                <div><dt class="text-sm text-slate-500">Tanggal Bergabung</dt><dd class="text-sm font-medium text-slate-900 mt-0.5">{{ $karyawan->tanggal_bergabung?->format('d/m/Y') ?? '-' }}</dd></div>
                <div><dt class="text-sm text-slate-500">Gaji Pokok</dt><dd class="text-sm font-medium text-slate-900 mt-0.5">Rp {{ number_format($karyawan->gaji_pokok, 0, ',', '.') }}</dd></div>
            </dl>
            <div class="mt-6">
                <button @click="editing = true" class="bg-indigo-600 text-white rounded-lg px-4 py-2.5 text-sm font-medium hover:bg-indigo-700 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit Profil
                </button>
            </div>
        </div>

        {{-- Edit Mode --}}
        <div x-show="editing" x-cloak>
            <form method="POST" action="{{ route('karyawan.profil.update') }}" @submit="loading = true">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="telepon" class="block text-sm font-medium text-slate-700 mb-1">Nomor Telepon</label>
                        <input type="text" id="telepon" name="telepon" value="{{ old('telepon', $karyawan->telepon) }}"
                               class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    </div>
                    <div class="md:col-span-2">
                        <label for="alamat" class="block text-sm font-medium text-slate-700 mb-1">Alamat</label>
                        <textarea id="alamat" name="alamat" rows="3"
                                  class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">{{ old('alamat', $karyawan->alamat) }}</textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="editing = false" class="border border-slate-200 text-slate-700 rounded-lg px-4 py-2.5 text-sm hover:bg-slate-50">Batal</button>
                    <button type="submit" :disabled="loading" class="bg-indigo-600 text-white rounded-lg px-4 py-2.5 text-sm font-medium hover:bg-indigo-700 disabled:opacity-50 flex items-center gap-2">
                        <svg x-show="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
