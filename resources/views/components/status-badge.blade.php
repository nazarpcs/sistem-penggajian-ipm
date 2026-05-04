{{-- Status Badge Component
     Usage: @include('components.status-badge', ['status' => 'hadir'])
--}}
@php
    $statusMap = [
        'hadir'              => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'label' => 'Hadir'],
        'izin'               => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'label' => 'Izin'],
        'sakit'              => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'label' => 'Sakit'],
        'alpha'              => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'label' => 'Alpha'],
        'menunggu_approval'  => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'label' => 'Menunggu Approval'],
        'disetujui'          => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'label' => 'Disetujui'],
        'ditolak'            => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'label' => 'Ditolak'],
        'draft'              => ['bg' => 'bg-slate-100', 'text' => 'text-slate-700', 'label' => 'Draft'],
        'final'              => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'label' => 'Final'],
        'aktif'              => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'label' => 'Aktif'],
        'terkunci'           => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'label' => 'Terkunci'],
    ];
    $s = $statusMap[strtolower($status ?? '')] ?? ['bg' => 'bg-slate-100', 'text' => 'text-slate-700', 'label' => ucfirst($status ?? '-')];
@endphp
<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $s['bg'] }} {{ $s['text'] }}">
    {{ $s['label'] }}
</span>
