@props(['id', 'title' => null])

<div
    x-data="{ open: false }"
    x-show="open"
    x-on:open-modal.window="$event.detail === '{{ $id }}' ? open = true : null"
    x-on:close-modal.window="$event.detail === '{{ $id }}' ? open = false : null"
    x-on:keydown.escape.window="open = false"
    class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0"
    style="display: none;"
>
    <div
        x-show="open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75 transition-opacity"
    ></div>

    <div
        x-show="open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        class="relative mb-6 sm:mx-auto sm:w-full sm:max-w-lg rounded-lg bg-white dark:bg-gray-800 shadow-xl overflow-hidden"
    >
        @if ($title)
            <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $title }}</h2>
                <button type="button" x-on:click="open = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <span class="sr-only">Close</span>
                    &times;
                </button>
            </div>
        @endif

        <div class="px-6 py-4">
            {{ $slot }}
        </div>
    </div>
</div>
