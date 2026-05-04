@extends('layouts.app')

@section('title', 'Edit Karyawan')
@section('page-title', 'Edit Karyawan')

@section('content')
<div class="max-w-3xl" x-data="{ loading: false }">
    <div class="mb-6">
        <a href="{{ route('admin.karyawan.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali ke Daftar Karyawan
        </a>
    </div>

    <form method="POST" action="{{ route('admin.karyawan.update', $karyawan) }}" @submit="loading = true">
        @csrf
        @method('PUT')
        <div class="bg-white rounded-xl shadow-sm p-6 space-y-6">
            <h2 class="text-lg font-semibold text-slate-900 border-b border-slate-100 pb-4">Data Pribadi</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label for="nama_lengkap" class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap', $karyawan->nama_lengkap) }}" required
                           class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none @error('nama_lengkap') border-red-300 @enderror">
                    @error('nama_lengkap')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="nik" class="block text-sm font-medium text-slate-700 mb-1">NIK <span class="text-red-500">*</span></label>
                    <input type="text" id="nik" name="nik" value="{{ old('nik', $karyawan->nik) }}" required
                           class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none @error('nik') border-red-300 @enderror">
                    @error('nik')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="tanggal_lahir" class="block text-sm font-medium text-slate-700 mb-1">Tanggal Lahir</label>
                    <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir', $karyawan->tanggal_lahir?->format('Y-m-d')) }}"
                           class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" id="email" name="email" value="{{ old('email', $karyawan->user->email ?? '') }}" required
                           class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none @error('email') border-red-300 @enderror">
                    @error('email')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="telepon" class="block text-sm font-medium text-slate-700 mb-1">Nomor Telepon</label>
                    <input type="text" id="telepon" name="telepon" value="{{ old('telepon', $karyawan->telepon) }}"
                           class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                </div>
                <div class="md:col-span-2">
                    <label for="alamat" class="block text-sm font-medium text-slate-700 mb-1">Alamat</label>
                    <textarea id="alamat" name="alamat" rows="2"
                              class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">{{ old('alamat', $karyawan->alamat) }}</textarea>
                </div>
            </div>

            <h2 class="text-lg font-semibold text-slate-900 border-b border-slate-100 pb-4 pt-2">Data Kepegawaian</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="pt_klien_id" class="block text-sm font-medium text-slate-700 mb-1">PT Klien <span class="text-red-500">*</span></label>
                    <select id="pt_klien_id" name="pt_klien_id" required
                            class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        <option value="">Pilih PT Klien</option>
                        @foreach($ptKliens ?? [] as $pt)
                            <option value="{{ $pt->id }}" {{ old('pt_klien_id', $karyawan->pt_klien_id) == $pt->id ? 'selected' : '' }}>{{ $pt->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="jabatan" class="block text-sm font-medium text-slate-700 mb-1">Jabatan <span class="text-red-500">*</span></label>
                    <input type="text" id="jabatan" name="jabatan" value="{{ old('jabatan', $karyawan->jabatan) }}" required
                           class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                </div>
                <div>
                    <label for="gaji_pokok" class="block text-sm font-medium text-slate-700 mb-1">Gaji Pokok <span class="text-red-500">*</span></label>
                    <input type="number" id="gaji_pokok" name="gaji_pokok" value="{{ old('gaji_pokok', $karyawan->gaji_pokok) }}" required min="0"
                           class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                </div>
                <div>
                    <label for="tanggal_bergabung" class="block text-sm font-medium text-slate-700 mb-1">Tanggal Bergabung</label>
                    <input type="date" id="tanggal_bergabung" name="tanggal_bergabung" value="{{ old('tanggal_bergabung', $karyawan->tanggal_bergabung?->format('Y-m-d')) }}"
                           class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                </div>
                <div>
                    <label for="status_aktif" class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                    <select id="status_aktif" name="status_aktif"
                            class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        <option value="1" {{ old('status_aktif', $karyawan->status_aktif) ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ !old('status_aktif', $karyawan->status_aktif) ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.karyawan.index') }}" class="border border-slate-200 text-slate-700 rounded-lg px-6 py-2.5 text-sm hover:bg-slate-50">Batal</a>
                <button type="submit" :disabled="loading"
                        class="bg-indigo-600 text-white rounded-lg px-6 py-2.5 text-sm font-medium hover:bg-indigo-700 disabled:opacity-50 flex items-center gap-2">
                    <svg x-show="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    Perbarui
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
