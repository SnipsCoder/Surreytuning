<x-layouts.owner>
    <x-page-header title="Dashboard" subtitle="Overview of file requests" />

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-stat-card label="Pending File Requests" :value="$pendingFileRequestsCount" colour="yellow" />
        <x-stat-card label="File Requests Today" :value="$fileRequestsTodayCount" colour="blue" />
        <x-stat-card label="Active Dealers" :value="$activeDealersCount" colour="green" />
        <x-stat-card label="Revenue This Month" :value="'£'.number_format($revenueThisMonth, 2)" colour="gray" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        <div class="lg:col-span-2">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Recent File Requests</h2>
                @if ($pendingApplicationsCount > 0)
                    <a href="{{ route('dealer-applications.index') }}" class="inline-flex items-center px-3 py-1.5 rounded-md text-sm font-medium bg-brand/10 text-brand">
                        {{ $pendingApplicationsCount }} pending dealer application{{ $pendingApplicationsCount === 1 ? '' : 's' }}
                    </a>
                @endif
            </div>

            <x-data-table :headers="['Request #', 'Dealer', 'Vehicle', 'Status', 'Created']">
                @forelse ($recentFileRequests as $fileRequest)
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                            <a href="{{ route('file-requests.show', $fileRequest) }}" class="text-brand hover:underline">
                                {{ $fileRequest->request_number_formatted }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $fileRequest->dealer?->company_name }}</td>
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
        </div>

        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Top Dealers <span class="text-sm font-normal text-gray-500 dark:text-gray-400">All Time</span></h2>
                </div>
                <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse ($topDealers as $dealer)
                        <li>
                            <a href="{{ route('dealers.show', $dealer) }}" class="flex items-center gap-3 px-6 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <span class="flex items-center justify-center w-8 h-8 rounded-md bg-brand text-white text-sm font-semibold shrink-0">
                                    {{ $loop->iteration }}
                                </span>
                                <span class="min-w-0">
                                    <span class="block text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ $dealer->company_name }}</span>
                                    <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $dealer->file_requests_count }} {{ Str::plural('File', $dealer->file_requests_count) }}</span>
                                </span>
                            </a>
                        </li>
                    @empty
                        <li class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">No dealer activity yet.</li>
                    @endforelse
                </ul>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Vehicle Stats Lookup</h2>
                    <a href="{{ route('vehicle-stats.index') }}" class="text-sm font-medium text-brand hover:underline">View all &rarr;</a>
                </div>
                <form method="GET" action="{{ route('vehicle-stats.index') }}" class="px-6 py-4 space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Make</label>
                        <select name="make"
                            class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm">
                            <option value="">Select a make&hellip;</option>
                            @foreach ($vehicleMakes as $make)
                                <option value="{{ $make }}">{{ $make }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Model</label>
                        <input type="text" name="model" placeholder="e.g. 147"
                            class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fuel</label>
                        <select name="fuel"
                            class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm">
                            <option value="">All fuel types</option>
                            @foreach (\App\Enums\FuelType::cases() as $fuel)
                                @continue($fuel === \App\Enums\FuelType::Hybrid)
                                <option value="{{ $fuel->value }}">{{ $fuel->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 w-full px-4 py-2 rounded-md bg-brand text-white text-sm font-medium hover:bg-brand-dark transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Look Up
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.owner>
