@extends('layouts.app')

@section('title', 'Audit Log')
@section('page-title', 'Audit Log')

@section('content')
<div class="space-y-6">
    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="GET" action="{{ route('admin.audit-log.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <label for="jenis_aktivitas" class="block text-sm font-medium text-slate-700 mb-1">Jenis Aktivitas</label>
                <select id="jenis_aktivitas" name="jenis_aktivitas" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    <option value="">Semua</option>
                    @foreach($jenisAktivitasList ?? [] as $jenis)
                        <option value="{{ $jenis }}" {{ ($filters['jenis_aktivitas'] ?? '') == $jenis ? 'selected' : '' }}>{{ $jenis }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="tanggal_dari" class="block text-sm font-medium text-slate-700 mb-1">Dari Tanggal</label>
                <input type="date" id="tanggal_dari" name="tanggal_dari" value="{{ $filters['tanggal_dari'] ?? '' }}"
                       class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
            </div>
            <div>
                <label for="tanggal_sampai" class="block text-sm font-medium text-slate-700 mb-1">Sampai Tanggal</label>
                <input type="date" id="tanggal_sampai" name="tanggal_sampai" value="{{ $filters['tanggal_sampai'] ?? '' }}"
                       class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="bg-indigo-600 text-white rounded-lg px-4 py-2.5 text-sm font-medium hover:bg-indigo-700">Filter</button>
                <a href="{{ route('admin.audit-log.index') }}" class="border border-slate-200 text-slate-700 rounded-lg px-4 py-2.5 text-sm hover:bg-slate-50">Reset</a>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Waktu</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Pengguna</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Role</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Aktivitas</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Model</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($auditLogs ?? [] as $log)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $log->created_at ? $log->created_at->format('d/m/Y H:i:s') : '-' }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ $log->user->name ?? 'Guest' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $log->role_pengguna ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $log->jenis_aktivitas }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $log->model_tipe ? $log->model_tipe . ' #' . $log->model_id : '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $log->ip_address ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-sm text-slate-500">Tidak ada data audit log.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($auditLogs ?? collect(), 'links'))
        <div class="px-4 py-3 border-t border-slate-100">{{ $auditLogs->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection
