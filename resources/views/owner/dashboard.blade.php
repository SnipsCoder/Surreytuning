<x-layouts.owner>
    <x-page-header title="Dashboard" subtitle="Overview of file requests" />

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-stat-card label="Pending File Requests" :value="$pendingFileRequestsCount" colour="yellow" />
        <x-stat-card label="File Requests Today" :value="$fileRequestsTodayCount" colour="blue" />
        <x-stat-card label="Active Dealers" :value="$activeDealersCount" colour="green" />
        <x-stat-card label="Revenue This Month" :value="'£'.number_format($revenueThisMonth, 2)" colour="gray" />
    </div>

    <div class="mb-4 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Recent File Requests</h2>
        @if ($pendingApplicationsCount > 0)
            <a href="{{ route('dealer-applications.index') }}" class="inline-flex items-center px-3 py-1.5 rounded-md text-sm font-medium bg-[#e63012]/10 text-[#e63012]">
                {{ $pendingApplicationsCount }} pending dealer application{{ $pendingApplicationsCount === 1 ? '' : 's' }}
            </a>
        @endif
    </div>

    <x-data-table :headers="['Request #', 'Dealer', 'Vehicle', 'Status', 'Created']">
        @forelse ($recentFileRequests as $fileRequest)
            <tr>
                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                    <a href="{{ route('file-requests.show', $fileRequest) }}" class="text-[#e63012] hover:underline">
                        {{ $fileRequest->request_number_formatted }}
                    </a>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $fileRequest->dealer?->name }}</td>
                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $fileRequest->make }} {{ $fileRequest->model }}</td>
                <td class="px-6 py-4 text-sm">
                    <x-status-badge :status="$fileRequest->status->label()" :colour="$fileRequest->status->colour()" />
                </td>
                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $fileRequest->created_at->format('d/m/Y H:i') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="px-6 py-4 text-sm text-center text-gray-500 dark:text-gray-400">No file requests yet.</td>
            </tr>
        @endforelse
    </x-data-table>
</x-layouts.owner>
