@extends('layouts.app')

@section('title', 'Detail Karyawan')
@section('page-title', 'Detail Karyawan')

@section('content')
<div class="max-w-4xl space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.karyawan.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali
        </a>
        <a href="{{ route('admin.karyawan.edit', $karyawan) }}" class="bg-indigo-600 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-indigo-700 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Edit
        </a>
    </div>

    {{-- Profile Header --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-2xl font-bold">
                {{ strtoupper(substr($karyawan->nama_lengkap, 0, 1)) }}
            </div>
            <div>
                <h2 class="text-xl font-semibold text-slate-900">{{ $karyawan->nama_lengkap }}</h2>
                <p class="text-sm text-slate-500">{{ $karyawan->jabatan }} — {{ $karyawan->ptKlien->nama ?? '-' }}</p>
                <div class="mt-1">@include('components.status-badge', ['status' => $karyawan->status_aktif ? 'aktif' : 'alpha'])</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Data Pribadi --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Data Pribadi</h3>
            <dl class="space-y-3">
                <div class="flex justify-between"><dt class="text-sm text-slate-500">NIK</dt><dd class="text-sm font-medium text-slate-900">{{ $karyawan->nik }}</dd></div>
                <div class="flex justify-between"><dt class="text-sm text-slate-500">Tanggal Lahir</dt><dd class="text-sm font-medium text-slate-900">{{ $karyawan->tanggal_lahir?->format('d/m/Y') ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-sm text-slate-500">Email</dt><dd class="text-sm font-medium text-slate-900">{{ $karyawan->user->email ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-sm text-slate-500">Telepon</dt><dd class="text-sm font-medium text-slate-900">{{ $karyawan->telepon ?? '-' }}</dd></div>
                <div><dt class="text-sm text-slate-500 mb-1">Alamat</dt><dd class="text-sm text-slate-900">{{ $karyawan->alamat ?? '-' }}</dd></div>
            </dl>
        </div>

        {{-- Data Kepegawaian --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Data Kepegawaian</h3>
            <dl class="space-y-3">
                <div class="flex justify-between"><dt class="text-sm text-slate-500">PT Klien</dt><dd class="text-sm font-medium text-slate-900">{{ $karyawan->ptKlien->nama ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-sm text-slate-500">Jabatan</dt><dd class="text-sm font-medium text-slate-900">{{ $karyawan->jabatan }}</dd></div>
                <div class="flex justify-between"><dt class="text-sm text-slate-500">Gaji Pokok</dt><dd class="text-sm font-medium text-slate-900">Rp {{ number_format($karyawan->gaji_pokok, 0, ',', '.') }}</dd></div>
                <div class="flex justify-between"><dt class="text-sm text-slate-500">Tanggal Bergabung</dt><dd class="text-sm font-medium text-slate-900">{{ $karyawan->tanggal_bergabung?->format('d/m/Y') ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-sm text-slate-500">Status</dt><dd>@include('components.status-badge', ['status' => $karyawan->status_aktif ? 'aktif' : 'alpha'])</dd></div>
            </dl>
        </div>
    </div>
</div>
@endsection
