@props(['title', 'subtitle' => null])

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $subtitle }}</p>
        @endif
    </div>

    @isset($slot)
        <div class="flex items-center gap-3">
            {{ $slot }}
        </div>
    @endisset
</div>
