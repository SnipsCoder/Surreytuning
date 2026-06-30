@props(['status', 'colour' => 'gray'])

@php
    $colourMap = [
        'green'  => 'bg-green-500/10 text-green-400 ring-1 ring-green-500/30',
        'yellow' => 'bg-amber-500/10 text-amber-400 ring-1 ring-amber-500/30',
        'red'    => 'bg-red-500/10 text-red-400 ring-1 ring-red-500/30',
        'blue'   => 'bg-blue-500/10 text-blue-400 ring-1 ring-blue-500/30',
        'gray'   => 'bg-slate-500/10 text-slate-400 ring-1 ring-slate-500/30',
        'orange' => 'bg-orange-500/10 text-orange-400 ring-1 ring-orange-500/30',
    ];
    $classes = $colourMap[$colour] ?? $colourMap['gray'];
@endphp

<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $classes }}">
    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
    {{ $status }}
</span>
