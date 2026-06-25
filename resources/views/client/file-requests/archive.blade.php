<x-layouts.client>
    <x-page-header title="Archive" subtitle="Closed and void file requests" />

    <form method="GET" action="{{ route('client.file-requests.archive') }}" class="mb-4">
        <input
            type="text"
            name="search"
            value="{{ request('search') }}"
            placeholder="Search by request number, registration, make or model..."
            class="block w-full sm:w-96 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm"
        >
    </form>

    @if ($fileRequests->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-sm text-gray-500 dark:text-gray-400">
            No archived file requests found.
        </div>
    @else
        <x-data-table :headers="['Request #', 'Vehicle', 'Status', 'Closed', '']">
            @foreach ($fileRequests as $fileRequest)
                <tr>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                        {{ $fileRequest->request_number_formatted }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                        {{ $fileRequest->make }} {{ $fileRequest->model }} ({{ $fileRequest->year }})
                    </td>
                    <td class="px-6 py-4">
                        <x-status-badge :status="$fileRequest->status->label()" :colour="$fileRequest->status->colour()" />
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                        {{ $fileRequest->closed_at?->format('d/m/Y') ?? '-' }}
                    </td>
                    <td class="px-6 py-4 text-right text-sm">
                        <a href="{{ route('client.file-requests.show', $fileRequest) }}" class="text-orange-600 hover:text-orange-800">View</a>
                    </td>
                </tr>
            @endforeach
        </x-data-table>
    @endif
</x-layouts.client>
