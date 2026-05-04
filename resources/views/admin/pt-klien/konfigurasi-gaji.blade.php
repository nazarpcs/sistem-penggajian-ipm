@extends('layouts.app')

@section('title', 'Konfigurasi Gaji')
@section('page-title', 'Konfigurasi Gaji — ' . $ptKlien->nama)

@section('content')
<div class="max-w-3xl" x-data="{
    loading: false,
    tunjangan: @json(old('komponen_tunjangan', $konfigurasi->komponen_tunjangan ?? [])),
    addTunjangan() { this.tunjangan.push({ nama: '', nilai: 0 }) },
    removeTunjangan(i) { this.tunjangan.splice(i, 1) }
}">
    <div class="mb-6">
        <a href="{{ route('admin.pt-klien.show', $ptKlien) }}" class="text-sm text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali ke Detail PT Klien
        </a>
    </div>

    <form method="POST" action="{{ route('admin.pt-klien.konfigurasi-gaji.update', $ptKlien) }}" @submit="loading = true">
        @csrf
        @method('PUT')
        <div class="bg-white rounded-xl shadow-sm p-6 space-y-6">
            <h2 class="text-lg font-semibold text-slate-900 border-b border-slate-100 pb-4">Pengaturan Gaji</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="gaji_pokok_default" class="block text-sm font-medium text-slate-700 mb-1">Gaji Pokok Default (Rp)</label>
                    <input type="number" id="gaji_pokok_default" name="gaji_pokok_default" min="0"
                           value="{{ old('gaji_pokok_default', $konfigurasi->gaji_pokok_default ?? 0) }}"
                           class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                </div>
                <div>
                    <label for="jam_kerja_normal" class="block text-sm font-medium text-slate-700 mb-1">Jam Kerja Normal / Hari</label>
                    <input type="number" id="jam_kerja_normal" name="jam_kerja_normal" min="0" step="0.5"
                           value="{{ old('jam_kerja_normal', $konfigurasi->jam_kerja_normal ?? 8) }}"
                           class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                </div>
                <div>
                    <label for="tarif_lembur_per_jam" class="block text-sm font-medium text-slate-700 mb-1">Tarif Lembur / Jam (Rp)</label>
                    <input type="number" id="tarif_lembur_per_jam" name="tarif_lembur_per_jam" min="0"
                           value="{{ old('tarif_lembur_per_jam', $konfigurasi->tarif_lembur_per_jam ?? 0) }}"
                           class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                </div>
                <div>
                    <label for="potongan_per_hari" class="block text-sm font-medium text-slate-700 mb-1">Potongan / Hari Alpha (Rp)</label>
                    <input type="number" id="potongan_per_hari" name="potongan_per_hari" min="0"
                           value="{{ old('potongan_per_hari', $konfigurasi->potongan_per_hari ?? 0) }}"
                           class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                </div>
            </div>

            {{-- Komponen Tunjangan Dinamis --}}
            <h2 class="text-lg font-semibold text-slate-900 border-b border-slate-100 pb-4 pt-2">Komponen Tunjangan</h2>
            <div class="space-y-3">
                <template x-for="(item, index) in tunjangan" :key="index">
                    <div class="flex items-center gap-3">
                        <div class="flex-1">
                            <input type="text" :name="'komponen_tunjangan['+index+'][nama]'" x-model="item.nama" placeholder="Nama tunjangan"
                                   class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        </div>
                        <div class="w-48">
                            <input type="number" :name="'komponen_tunjangan['+index+'][nilai]'" x-model="item.nilai" placeholder="Nilai (Rp)" min="0"
                                   class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        </div>
                        <button type="button" @click="removeTunjangan(index)" class="p-2 rounded-lg hover:bg-red-50 text-red-500" title="Hapus">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </template>
                <div x-show="tunjangan.length === 0" class="text-sm text-slate-400 py-2">Belum ada komponen tunjangan.</div>
                <button type="button" @click="addTunjangan()" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Tunjangan
                </button>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.pt-klien.show', $ptKlien) }}" class="border border-slate-200 text-slate-700 rounded-lg px-6 py-2.5 text-sm hover:bg-slate-50">Batal</a>
                <button type="submit" :disabled="loading" class="bg-indigo-600 text-white rounded-lg px-6 py-2.5 text-sm font-medium hover:bg-indigo-700 disabled:opacity-50 flex items-center gap-2">
                    <svg x-show="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    Simpan Konfigurasi
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
