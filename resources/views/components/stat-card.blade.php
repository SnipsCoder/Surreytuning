@props(['label', 'value', 'colour' => 'blue', 'icon' => null])

@php
    $colours = [
        'blue' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
        'green' => 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300',
        'red' => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300',
        'yellow' => 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
        'gray' => 'bg-gray-50 text-gray-700 dark:bg-gray-900/30 dark:text-gray-300',
    ];

    $colourClasses = $colours[$colour] ?? $colours['blue'];
@endphp

<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5 flex items-center gap-4">
    @if ($icon)
        <div class="flex-shrink-0 w-10 h-10 rounded-md flex items-center justify-center {{ $colourClasses }}">
            {{ $icon }}
        </div>
    @endif

    <div>
        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $label }}</p>
        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $value }}</p>
    </div>
</div>
