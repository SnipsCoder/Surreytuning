<x-layouts.owner>
    <div x-data="{ view: localStorage.getItem('frq-view') || 'board' }" x-init="$watch('view', value => localStorage.setItem('frq-view', value))">
        <x-page-header title="File Requests" subtitle="Active tuning file requests across all dealers">
            <div class="flex items-center gap-2">
                <div class="inline-flex rounded-md border border-gray-300 dark:border-gray-700 overflow-hidden">
                    <button type="button" x-on:click="view = 'board'" :class="view === 'board' ? 'bg-gray-900 text-white dark:bg-gray-700' : 'bg-white text-gray-600 dark:bg-gray-800 dark:text-gray-300'" class="px-3 py-1.5 text-sm font-medium">
                        Board
                    </button>
                    <button type="button" x-on:click="view = 'list'" :class="view === 'list' ? 'bg-gray-900 text-white dark:bg-gray-700' : 'bg-white text-gray-600 dark:bg-gray-800 dark:text-gray-300'" class="px-3 py-1.5 text-sm font-medium">
                        List
                    </button>
                </div>
                <a href="{{ route('owner.file-requests.archive') }}" class="inline-flex items-center px-3 py-1.5 rounded-md border border-gray-300 dark:border-gray-700 text-sm font-medium text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                    Archive
                </a>
            </div>
        </x-page-header>

        <form method="GET" action="{{ route('file-requests.index') }}" class="mb-6 flex flex-wrap items-center gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search request #, make, model, dealer..."
                class="flex-1 min-w-[220px] rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">

            <select name="status" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm shadow-sm">
                <option value="">All statuses</option>
                @foreach ($statuses as $status)
                    @continue(in_array($status, [\App\Enums\FileRequestStatus::Closed, \App\Enums\FileRequestStatus::Void], true))
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>

            <select name="assigned_technician_id" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm shadow-sm">
                <option value="">All technicians</option>
                @foreach ($technicians as $technician)
                    <option value="{{ $technician->id }}" @selected((string) request('assigned_technician_id') === (string) $technician->id)>{{ $technician->full_name }}</option>
                @endforeach
            </select>

            <button type="submit" class="px-4 py-2 rounded-md bg-gray-900 dark:bg-gray-700 text-white text-sm font-medium hover:bg-gray-800 dark:hover:bg-gray-600">
                Filter
            </button>

            @if (request('search') || request('status') || request('assigned_technician_id'))
                <a href="{{ route('file-requests.index') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:underline">Clear</a>
            @endif
        </form>

        {{-- Board view --}}
        <div x-show="view === 'board'" x-cloak class="overflow-x-auto pb-4">
            <div class="flex gap-4 min-w-max">
                @foreach ($statuses as $status)
                    @continue(in_array($status, [\App\Enums\FileRequestStatus::Closed, \App\Enums\FileRequestStatus::Void], true))
                    @php $columnRequests = $fileRequests->getCollection()->where('status', $status); @endphp
                    <div class="w-72 flex-shrink-0 bg-gray-100 dark:bg-gray-800/60 rounded-lg p-3">
                        <div class="flex items-center justify-between mb-3 px-1">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full {{ str($status->colour())->before(' ') }}"></span>
                                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ $status->label() }}</h3>
                            </div>
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $columnRequests->count() }}</span>
                        </div>

                        <div class="space-y-2">
                            @foreach ($columnRequests->take(5) as $fileRequest)
                                <a href="{{ route('file-requests.show', $fileRequest) }}" class="block bg-white dark:bg-gray-800 rounded-md p-3 shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md transition">
                                    <div class="flex items-center gap-2 mb-2">
                                        <div class="relative w-6 h-6 flex-shrink-0">
                                            <img
                                                src="https://logo.clearbit.com/{{ \Illuminate\Support\Str::slug($fileRequest->make) }}.com"
                                                alt="{{ $fileRequest->make }}"
                                                class="w-6 h-6 rounded object-contain bg-white"
                                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                            >
                                            <span style="display:none;" class="w-6 h-6 rounded bg-gray-200 dark:bg-gray-700 items-center justify-center absolute inset-0">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-gray-500 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 17h14M5 17a2 2 0 100 4 2 2 0 000-4zm14 0a2 2 0 100 4 2 2 0 000-4zM5 17l1.5-5h11L19 17M6.5 12l1-3.5A2 2 0 019.4 7h5.2a2 2 0 011.9 1.5l1 3.5" />
                                                </svg>
                                            </span>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ $fileRequest->make }} {{ $fileRequest->model }}</span>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ $fileRequest->request_number_formatted }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $fileRequest->dealer?->company_name }}</p>
                                </a>
                            @endforeach

                            @if ($columnRequests->count() > 5)
                                <a href="{{ route('file-requests.index', array_merge(request()->query(), ['status' => $status->value])) }}" class="block text-center text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:underline py-1">
                                    +{{ $columnRequests->count() - 5 }} more
                                </a>
                            @endif

                            @if ($columnRequests->isEmpty())
                                <p class="text-xs text-gray-400 dark:text-gray-500 text-center py-4">No requests</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- List view --}}
        <div x-show="view === 'list'" x-cloak>
            <x-data-table :headers="['Request #', 'Vehicle', 'Dealer', 'Stage', 'Technician', 'Status', '']">
                @forelse ($fileRequests as $fileRequest)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/60">
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $fileRequest->request_number_formatted }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $fileRequest->make }} {{ $fileRequest->model }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $fileRequest->dealer?->company_name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $fileRequest->fileStage?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $fileRequest->assignedTechnician?->full_name ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm">
                            <x-status-badge :status="$fileRequest->status->label()" :colour="$fileRequest->status->colour()" />
                        </td>
                        <td class="px-4 py-3 text-sm text-right">
                            <a href="{{ route('file-requests.show', $fileRequest) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No file requests found.</td>
                    </tr>
                @endforelse
            </x-data-table>

            <div class="mt-4">
                {{ $fileRequests->links() }}
            </div>
        </div>
    </div>
</x-layouts.owner>
