<x-layouts.owner>
    <x-page-header title="File Archive" subtitle="Closed and voided requests older than 90 days">
        <a href="{{ route('file-requests.index') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:underline">
            Back to active requests
        </a>
    </x-page-header>

    <form method="GET" action="{{ route('owner.file-requests.archive') }}" class="mb-6 flex flex-wrap items-center gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search request #, make, model, dealer..."
            class="flex-1 min-w-[220px] rounded-md border-gray-300 dark:border-[#2a2a2a] dark:bg-[#1a1a1a] dark:text-gray-100 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">

        <button type="submit" class="px-4 py-2 rounded-md bg-[#0d0d0d] dark:bg-gray-700 text-white text-sm font-medium hover:bg-[#1a1a1a] dark:hover:bg-gray-600">
            Search
        </button>

        @if (request('search'))
            <a href="{{ route('owner.file-requests.archive') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:underline">Clear</a>
        @endif
    </form>

    <x-data-table :headers="['Request #', 'Vehicle', 'Dealer', 'Status', 'Closed At', '']">
        @forelse ($fileRequests as $fileRequest)
            <tr class="hover:bg-gray-50 dark:hover:bg-[#1a1a1a]/60">
                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $fileRequest->request_number_formatted }}</td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $fileRequest->make }} {{ $fileRequest->model }}</td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $fileRequest->dealer?->company_name }}</td>
                <td class="px-4 py-3 text-sm">
                    <x-status-badge :status="$fileRequest->status->label()" :colour="$fileRequest->status->colour()" />
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $fileRequest->closed_at?->format('d M Y') ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-right">
                    <a href="{{ route('file-requests.show', $fileRequest) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">View</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No archived requests found.</td>
            </tr>
        @endforelse
    </x-data-table>

    <div class="mt-4">
        {{ $fileRequests->links() }}
    </div>
</x-layouts.owner>
