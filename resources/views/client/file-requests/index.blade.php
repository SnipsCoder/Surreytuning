<x-layouts.client>
    <x-page-header title="File Requests" subtitle="Your active jobs">
        <a href="{{ route('client.upload.create') }}" class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700">
            New File Request
        </a>
    </x-page-header>

    <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
        <form method="GET" action="{{ route('client.file-requests.index') }}" class="flex items-center gap-2">
            <input type="hidden" name="view" value="{{ $view }}">
            <select name="status" onchange="this.form.submit()" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm">
                <option value="">All Statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </form>

        <div class="inline-flex rounded-md shadow-sm">
            <a href="{{ request()->fullUrlWithQuery(['view' => 'table']) }}" class="px-3 py-2 text-sm font-medium rounded-l-md border border-gray-300 dark:border-gray-600 {{ $view === 'table' ? 'bg-orange-600 text-white border-orange-600' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200' }}">
                Table
            </a>
            <a href="{{ request()->fullUrlWithQuery(['view' => 'kanban']) }}" class="px-3 py-2 text-sm font-medium rounded-r-md border-t border-b border-r border-gray-300 dark:border-gray-600 {{ $view === 'kanban' ? 'bg-orange-600 text-white border-orange-600' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200' }}">
                Kanban
            </a>
        </div>
    </div>

    @if ($fileRequests->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-sm text-gray-500 dark:text-gray-400">
            No file requests found.
        </div>
    @elseif ($view === 'kanban')
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ($statuses as $status)
                @php $grouped = $fileRequests->where('status', $status); @endphp
                @if ($grouped->isNotEmpty())
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 flex items-center justify-between">
                            {{ $status->label() }}
                            <span class="text-xs text-gray-400">{{ $grouped->count() }}</span>
                        </h3>
                        <div class="space-y-3">
                            @foreach ($grouped as $fileRequest)
                                <a href="{{ route('client.file-requests.show', $fileRequest) }}" class="block bg-white dark:bg-gray-800 rounded-lg shadow p-4 hover:ring-1 hover:ring-orange-500">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $fileRequest->request_number_formatted }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $fileRequest->make }} {{ $fileRequest->model }} ({{ $fileRequest->year }})</p>
                                    <p class="mt-2 text-xs text-gray-400">{{ $fileRequest->created_at->format('d/m/Y') }}</p>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @else
        <x-data-table :headers="['Request #', 'Vehicle', 'Status', 'Submitted', '']">
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
                        {{ $fileRequest->created_at->format('d/m/Y') }}
                    </td>
                    <td class="px-6 py-4 text-right text-sm">
                        <a href="{{ route('client.file-requests.show', $fileRequest) }}" class="text-orange-600 hover:text-orange-800">View</a>
                    </td>
                </tr>
            @endforeach
        </x-data-table>
    @endif
</x-layouts.client>
