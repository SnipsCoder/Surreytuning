<x-layouts.client>
    <x-page-header title="Dashboard" subtitle="Welcome back, {{ auth()->user()->first_name }}" />

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <x-stat-card label="Pending" :value="$stats['pending']" colour="yellow" />
        <x-stat-card label="In Progress" :value="$stats['in_progress']" colour="blue" />
        <x-stat-card label="Completed This Year" :value="$stats['completed_this_year']" colour="green" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Recent File Requests</h2>

            @if ($recentFileRequests->isEmpty())
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-sm text-gray-500 dark:text-gray-400">
                    You haven't submitted any file requests yet.
                </div>
            @else
                <x-data-table :headers="['Request #', 'Vehicle', 'Status', 'Submitted', '']">
                    @foreach ($recentFileRequests as $fileRequest)
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
                                {{ $fileRequest->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 text-right text-sm">
                                <a href="{{ route('client.file-requests.show', $fileRequest) }}" class="text-orange-600 hover:text-orange-800">View</a>
                            </td>
                        </tr>
                    @endforeach
                </x-data-table>
            @endif
        </div>

        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Portal Status</h3>
                <x-status-badge :status="$portalStatus->status->label()" :colour="$portalStatus->status->colour()" />
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Today's Opening Hours</h3>
                @if ($todayHours && $todayHours->is_open)
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ \Illuminate\Support\Carbon::parse($todayHours->open_time)->format('H:i') }} -
                        {{ \Illuminate\Support\Carbon::parse($todayHours->close_time)->format('H:i') }}
                    </p>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">Closed today</p>
                @endif
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Notices</h3>
                @if ($notices->isEmpty())
                    <p class="text-sm text-gray-500 dark:text-gray-400">No current notices.</p>
                @else
                    <div class="space-y-3">
                        @foreach ($notices as $notice)
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $notice->title }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $notice->body }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.client>
