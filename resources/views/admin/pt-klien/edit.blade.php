@extends('layouts.app')

@section('title', 'Edit PT Klien')
@section('page-title', 'Edit PT Klien')

@section('content')
<div class="max-w-3xl" x-data="{ loading: false }">
    <div class="mb-6">
        <a href="{{ route('admin.pt-klien.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali
        </a>
    </div>

    <form method="POST" action="{{ route('admin.pt-klien.update', $ptKlien) }}" @submit="loading = true">
        @csrf
        @method('PUT')
        <div class="bg-white rounded-xl shadow-sm p-6 space-y-6">
            <h2 class="text-lg font-semibold text-slate-900 border-b border-slate-100 pb-4">Informasi Perusahaan</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label for="nama" class="block text-sm font-medium text-slate-700 mb-1">Nama Perusahaan <span class="text-red-500">*</span></label>
                    <input type="text" id="nama" name="nama" value="{{ old('nama', $ptKlien->nama) }}" required
                           class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none @error('nama') border-red-300 @enderror">
                    @error('nama')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label for="alamat" class="block text-sm font-medium text-slate-700 mb-1">Alamat</label>
                    <textarea id="alamat" name="alamat" rows="2" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">{{ old('alamat', $ptKlien->alamat) }}</textarea>
                </div>
                <div>
                    <label for="telepon" class="block text-sm font-medium text-slate-700 mb-1">Telepon</label>
                    <input type="text" id="telepon" name="telepon" value="{{ old('telepon', $ptKlien->telepon) }}" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $ptKlien->email) }}" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                </div>
                <div>
                    <label for="nama_pic" class="block text-sm font-medium text-slate-700 mb-1">Nama PIC <span class="text-red-500">*</span></label>
                    <input type="text" id="nama_pic" name="nama_pic" value="{{ old('nama_pic', $ptKlien->nama_pic) }}" required class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                </div>
            </div>

            <h2 class="text-lg font-semibold text-slate-900 border-b border-slate-100 pb-4 pt-2">Data Kontrak</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="nomor_kontrak" class="block text-sm font-medium text-slate-700 mb-1">Nomor Kontrak</label>
                    <input type="text" id="nomor_kontrak" name="nomor_kontrak" value="{{ old('nomor_kontrak', $ptKlien->nomor_kontrak) }}" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                </div>
                <div>
                    <label for="fee_jasa" class="block text-sm font-medium text-slate-700 mb-1">Fee Jasa (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" id="fee_jasa" name="fee_jasa" value="{{ old('fee_jasa', $ptKlien->fee_jasa) }}" required min="0" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                </div>
                <div>
                    <label for="tgl_mulai" class="block text-sm font-medium text-slate-700 mb-1">Tanggal Mulai Kontrak</label>
                    <input type="date" id="tgl_mulai" name="tgl_mulai" value="{{ old('tgl_mulai', $ptKlien->tgl_mulai?->format('Y-m-d')) }}" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                </div>
                <div>
                    <label for="tgl_berakhir" class="block text-sm font-medium text-slate-700 mb-1">Tanggal Berakhir Kontrak</label>
                    <input type="date" id="tgl_berakhir" name="tgl_berakhir" value="{{ old('tgl_berakhir', $ptKlien->tgl_berakhir?->format('Y-m-d')) }}" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.pt-klien.index') }}" class="border border-slate-200 text-slate-700 rounded-lg px-6 py-2.5 text-sm hover:bg-slate-50">Batal</a>
                <button type="submit" :disabled="loading" class="bg-indigo-600 text-white rounded-lg px-6 py-2.5 text-sm font-medium hover:bg-indigo-700 disabled:opacity-50 flex items-center gap-2">
                    <svg x-show="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    Perbarui
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
