@php
    $flashes = [
        'success' => ['session' => session('success'), 'classes' => 'bg-green-50 text-green-800 border-green-200 dark:bg-green-900/30 dark:text-green-300 dark:border-green-800'],
        'error' => ['session' => session('error'), 'classes' => 'bg-red-50 text-red-800 border-red-200 dark:bg-red-900/30 dark:text-red-300 dark:border-red-800'],
        'warning' => ['session' => session('warning'), 'classes' => 'bg-yellow-50 text-yellow-800 border-yellow-200 dark:bg-yellow-900/30 dark:text-yellow-300 dark:border-yellow-800'],
    ];
@endphp

@foreach ($flashes as $type => $flash)
    @if ($flash['session'])
        <div
            x-data="{ show: true }"
            x-show="show"
            x-transition
            class="mb-4 flex items-center justify-between rounded-md border px-4 py-3 text-sm {{ $flash['classes'] }}"
        >
            <span>{{ $flash['session'] }}</span>
            <button type="button" x-on:click="show = false" class="ms-4 opacity-70 hover:opacity-100">
                &times;
            </button>
        </div>
    @endif
@endforeach
