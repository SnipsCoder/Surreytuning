<x-layouts.client>
    <x-page-header title="What's New" subtitle="Latest updates and releases" />

    @if ($whatsNews->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-8 text-center text-sm text-gray-500 dark:text-gray-400">
            No updates yet.
        </div>
    @else
        <div class="space-y-4">
            @foreach ($whatsNews as $item)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5">
                    <div class="flex items-center justify-between mb-2">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ $item->title }}</h2>
                        <div class="flex items-center gap-2 text-xs text-gray-400 dark:text-gray-500">
                            @if ($item->version)
                                <span class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-2 py-0.5 rounded-full">v{{ $item->version }}</span>
                            @endif
                            @if ($item->published_at)
                                <span>{{ $item->published_at->format('d M Y') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-300 whitespace-pre-wrap">{{ $item->body }}</div>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.client>
