@extends('layouts.app')

@section('title', 'Invoice')
@section('page-title', 'Invoice')

@section('content')
<div class="space-y-6" x-data="{ buatModal: false, buatLoading: false }">
    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="GET" action="{{ route('admin.invoice.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
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
                <label for="periode_id" class="block text-sm font-medium text-slate-700 mb-1">Periode</label>
                <select id="periode_id" name="periode_id" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    <option value="">Semua Periode</option>
                    @foreach($periodes ?? [] as $p)
                        <option value="{{ $p->id }}" {{ request('periode_id') == $p->id ? 'selected' : '' }}>{{ sprintf('%02d/%d', $p->bulan, $p->tahun) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                <select id="status" name="status" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    <option value="">Semua Status</option>
                    <option value="menunggu_approval" {{ request('status') == 'menunggu_approval' ? 'selected' : '' }}>Menunggu Approval</option>
                    <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="bg-indigo-600 text-white rounded-lg px-4 py-2.5 text-sm font-medium hover:bg-indigo-700">Filter</button>
                <a href="{{ route('admin.invoice.index') }}" class="border border-slate-200 text-slate-700 rounded-lg px-4 py-2.5 text-sm hover:bg-slate-50">Reset</a>
            </div>
            <div class="flex items-end justify-end">
                <button type="button" @click="buatModal = true" class="bg-indigo-600 text-white rounded-lg px-4 py-2.5 text-sm font-medium hover:bg-indigo-700 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Buat Invoice
                </button>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">No. Invoice</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">PT Klien</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Periode</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Total Tagihan</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($invoices ?? [] as $inv)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ $inv->nomor_invoice }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $inv->ptKlien->nama ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $inv->periodePenggajian ? sprintf('%02d/%d', $inv->periodePenggajian->bulan, $inv->periodePenggajian->tahun) : '-' }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-slate-900 text-right">Rp {{ number_format($inv->total_tagihan, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center">@include('components.status-badge', ['status' => $inv->status])</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $inv->tanggal_pembuatan ? \Carbon\Carbon::parse($inv->tanggal_pembuatan)->format('d/m/Y') : $inv->created_at->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('admin.invoice.show', $inv->id) }}" class="p-2 rounded-lg hover:bg-slate-100 text-slate-500 hover:text-indigo-600" title="Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                @if($inv->status === 'disetujui')
                                <a href="{{ route('admin.invoice.pdf', $inv->id) }}" target="_blank" class="p-2 rounded-lg hover:bg-slate-100 text-slate-500 hover:text-red-600" title="Download PDF">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-sm text-slate-500">Data invoice tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($invoices ?? collect(), 'links'))
        <div class="px-4 py-3 border-t border-slate-100">{{ $invoices->withQueryString()->links() }}</div>
        @endif
    </div>

    {{-- Buat Invoice Modal --}}
    <div x-show="buatModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" x-transition>
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="buatModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full mx-4 p-6">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Buat Invoice Baru</h3>
            <form method="POST" action="{{ route('admin.invoice.store') }}" @submit="buatLoading = true">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="inv_pt_klien_id" class="block text-sm font-medium text-slate-700 mb-1">PT Klien <span class="text-red-500">*</span></label>
                        <select id="inv_pt_klien_id" name="pt_klien_id" required class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                            <option value="">Pilih PT Klien</option>
                            @foreach($ptKliens ?? [] as $pt)
                                <option value="{{ $pt->id }}">{{ $pt->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="inv_periode_id" class="block text-sm font-medium text-slate-700 mb-1">Periode <span class="text-red-500">*</span></label>
                        <select id="inv_periode_id" name="periode_id" required class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                            <option value="">Pilih Periode</option>
                            @foreach($periodes ?? [] as $p)
                                <option value="{{ $p->id }}">{{ sprintf('%02d/%d', $p->bulan, $p->tahun) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="buatModal = false" class="border border-slate-200 text-slate-700 rounded-lg px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                    <button type="submit" :disabled="buatLoading" class="bg-indigo-600 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-indigo-700 disabled:opacity-50 flex items-center gap-2">
                        <svg x-show="buatLoading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <span x-text="buatLoading ? 'Membuat...' : 'Buat Invoice'">Buat Invoice</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
