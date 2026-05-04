@extends('layouts.app')

@section('title', 'Dashboard Pemilik PT')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">
    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @include('components.stat-card', [
            'title' => 'Total Pengeluaran Gaji Bulan Ini',
            'value' => 'Rp ' . number_format($totalPengeluaranBulanIni, 0, ',', '.'),
            'color' => 'indigo',
            'icon' => '<svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        ])
        @include('components.stat-card', [
            'title' => 'Invoice Menunggu Approval',
            'value' => $invoicePending->count(),
            'color' => 'amber',
            'icon' => '<svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'
        ])
    </div>

    {{-- Chart --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Tren Pengeluaran Gaji 12 Bulan Terakhir</h2>
        <div class="relative" style="height: 350px;">
            <canvas id="trendChart" aria-label="Grafik tren pengeluaran gaji 12 bulan terakhir" role="img"></canvas>
        </div>
    </div>

    {{-- Invoice Pending Table --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h2 class="text-lg font-semibold text-slate-900">Invoice Memerlukan Approval</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">No. Invoice</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">PT Klien</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Periode</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Total Tagihan</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($invoicePending as $inv)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ $inv->nomor_invoice }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $inv->ptKlien->nama ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">
                            {{ $inv->periodePenggajian ? sprintf('%02d/%d', $inv->periodePenggajian->bulan, $inv->periodePenggajian->tahun) : '-' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600 text-right">Rp {{ number_format($inv->total_tagihan, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('owner.invoice.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">Review</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">Tidak ada invoice menunggu approval.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('trendChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: 'Total Pengeluaran Gaji',
                data: @json($chartData),
                borderColor: '#4F46E5',
                backgroundColor: 'rgba(79, 70, 229, 0.08)',
                tension: 0.3,
                fill: true,
                pointRadius: 4,
                pointBackgroundColor: '#4F46E5',
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#F1F5F9' },
                    ticks: {
                        callback: function (value) {
                            if (value >= 1000000) return 'Rp ' + (value / 1000000).toFixed(0) + 'jt';
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                },
                x: { grid: { display: false } }
            }
        }
    });
});
</script>
@endpush
