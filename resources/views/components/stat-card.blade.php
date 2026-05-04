{{-- Stat Card Component
     Usage: @include('components.stat-card', ['title' => '...', 'value' => '...', 'color' => 'indigo', 'icon' => '<svg>...</svg>'])
     Colors: indigo, emerald, amber, red, blue, purple
--}}
@php
    $colorMap = [
        'indigo'  => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-600', 'icon' => 'text-indigo-600'],
        'emerald' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-600', 'icon' => 'text-emerald-600'],
        'amber'   => ['bg' => 'bg-amber-100', 'text' => 'text-amber-600', 'icon' => 'text-amber-600'],
        'red'     => ['bg' => 'bg-red-100', 'text' => 'text-red-600', 'icon' => 'text-red-600'],
        'blue'    => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600', 'icon' => 'text-blue-600'],
        'purple'  => ['bg' => 'bg-purple-100', 'text' => 'text-purple-600', 'icon' => 'text-purple-600'],
    ];
    $c = $colorMap[$color ?? 'indigo'];
@endphp
<div class="bg-white rounded-xl shadow-sm p-6">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-slate-500">{{ $title }}</p>
            <p class="text-3xl font-bold {{ $c['text'] }} mt-1">{{ $value }}</p>
            @if(!empty($subtitle))
                <p class="text-xs text-slate-400 mt-1">{{ $subtitle }}</p>
            @endif
        </div>
        <div class="{{ $c['bg'] }} rounded-full p-3">
            {!! $icon ?? '' !!}
        </div>
    </div>
</div>
