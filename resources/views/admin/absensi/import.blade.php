@extends('layouts.app')

@section('title', 'Import Absensi')
@section('page-title', 'Import Absensi Excel')

@section('content')
<div class="max-w-2xl" x-data="{
    loading: false,
    dragOver: false,
    fileName: '',
    fileSize: '',
    handleFile(e) {
        const file = e.target.files[0] || (e.dataTransfer && e.dataTransfer.files[0]);
        if (file) {
            this.fileName = file.name;
            this.fileSize = (file.size / 1024).toFixed(1) + ' KB';
            if (e.target.files) return;
            this.$refs.fileInput.files = e.dataTransfer.files;
        }
    }
}">
    <div class="mb-6">
        <a href="{{ route('admin.absensi.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali ke Data Absensi
        </a>
    </div>

    <form method="POST" action="{{ route('admin.absensi.import') }}" enctype="multipart/form-data" @submit="loading = true">
        @csrf
        <div class="bg-white rounded-xl shadow-sm p-6 space-y-6">
            <div>
                <h2 class="text-lg font-semibold text-slate-900 mb-2">Upload File Excel</h2>
                <p class="text-sm text-slate-500">Format yang didukung: .xlsx, .xls. Maksimal 5MB.</p>
            </div>

            {{-- Drag & Drop Area --}}
            <div @dragover.prevent="dragOver = true" @dragleave.prevent="dragOver = false"
                 @drop.prevent="dragOver = false; handleFile($event)"
                 :class="dragOver ? 'border-indigo-500 bg-indigo-50' : 'border-slate-300 bg-slate-50'"
                 class="border-2 border-dashed rounded-xl p-8 text-center transition-colors cursor-pointer"
                 @click="$refs.fileInput.click()">
                <input type="file" name="file" x-ref="fileInput" @change="handleFile($event)" accept=".xlsx,.xls" class="hidden" required>
                <div x-show="!fileName">
                    <svg class="w-12 h-12 mx-auto text-slate-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    <p class="text-sm text-slate-600 font-medium">Seret file ke sini atau klik untuk memilih</p>
                    <p class="text-xs text-slate-400 mt-1">File Excel (.xlsx, .xls) maksimal 5MB</p>
                </div>
                <div x-show="fileName" x-cloak class="flex items-center justify-center gap-3">
                    <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <div class="text-left">
                        <p class="text-sm font-medium text-slate-900" x-text="fileName"></p>
                        <p class="text-xs text-slate-500" x-text="fileSize"></p>
                    </div>
                </div>
            </div>

            @if($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg text-sm" role="alert">
                <p class="font-semibold mb-2">Terdapat kesalahan pada file:</p>
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.absensi.index') }}" class="border border-slate-200 text-slate-700 rounded-lg px-6 py-2.5 text-sm hover:bg-slate-50">Batal</a>
                <button type="submit" :disabled="loading || !fileName" class="bg-indigo-600 text-white rounded-lg px-6 py-2.5 text-sm font-medium hover:bg-indigo-700 disabled:opacity-50 flex items-center gap-2">
                    <svg x-show="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    <span x-text="loading ? 'Memproses...' : 'Upload & Import'">Upload & Import</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
