@props(['label', 'value', 'colour' => 'blue', 'icon' => null])

@php
    $accents = [
        'blue'   => 'text-blue-400',
        'green'  => 'text-green-400',
        'red'    => 'text-[#e63012]',
        'yellow' => 'text-amber-400',
        'gray'   => 'text-slate-400',
    ];
    $accentClass = $accents[$colour] ?? $accents['blue'];
@endphp

<div class="bg-[#1e293b] border border-gray-700/50 rounded-xl p-6 flex items-center gap-4">
    @if ($icon)
        <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center bg-white/5 {{ $accentClass }}">
            {!! $icon !!}
        </div>
    @endif

    <div>
        <p class="text-sm font-medium text-slate-400">{{ $label }}</p>
        <p class="text-2xl font-bold text-white mt-0.5">{{ $value }}</p>
    </div>
</div>
