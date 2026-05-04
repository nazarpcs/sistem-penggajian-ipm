@extends('layouts.app')

@section('title', 'Slip Gaji Saya')
@section('page-title', 'Slip Gaji Saya')

@section('content')
<div class="space-y-6">
    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h2 class="text-lg font-semibold text-slate-900">Daftar Slip Gaji</h2>
            <p class="text-sm text-slate-500 mt-1">Slip gaji Anda diurutkan dari periode terbaru</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Periode</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Gaji Pokok</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Tunjangan</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Lembur</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Potongan</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Gaji Bersih</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($slipGajis ?? [] as $slip)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ $slip->periode ? sprintf('%02d/%d', $slip->periode->bulan, $slip->periode->tahun) : '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600 text-right">Rp {{ number_format($slip->gaji_pokok, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-emerald-600 text-right">+Rp {{ number_format($slip->total_tunjangan, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-blue-600 text-right">+Rp {{ number_format($slip->total_lembur, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-red-600 text-right">-Rp {{ number_format($slip->total_potongan, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-slate-900 text-right">Rp {{ number_format($slip->gaji_bersih, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('karyawan.slip-gaji.pdf', $slip->id) }}" target="_blank"
                               class="inline-flex items-center gap-1 text-sm text-red-600 hover:text-red-700 font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                PDF
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-sm text-slate-500">Belum ada slip gaji tersedia.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($slipGajis ?? collect(), 'links'))
        <div class="px-4 py-3 border-t border-slate-100">{{ $slipGajis->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection
