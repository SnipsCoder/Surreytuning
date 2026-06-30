<x-layouts.client>
    <div x-data="{ view: localStorage.getItem('client-frq-view') || 'board' }" x-init="$watch('view', v => localStorage.setItem('client-frq-view', v))">

        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl font-bold text-white">File Requests</h1>
                <p class="text-sm text-slate-400 mt-0.5">Your active tuning jobs</p>
            </div>
            <div class="flex items-center gap-3">
                <!-- Board / List toggle -->
                <div class="inline-flex rounded-lg overflow-hidden border border-white/10">
                    <button type="button" x-on:click="view = 'board'"
                        :class="view === 'board' ? 'bg-[#e63012] text-white' : 'bg-[#1e293b] text-slate-400 hover:text-white'"
                        class="px-3 py-1.5 text-sm font-medium transition-colors">
                        Board
                    </button>
                    <button type="button" x-on:click="view = 'list'"
                        :class="view === 'list' ? 'bg-[#e63012] text-white' : 'bg-[#1e293b] text-slate-400 hover:text-white'"
                        class="px-3 py-1.5 text-sm font-medium transition-colors">
                        List
                    </button>
                </div>
                <a href="{{ route('client.upload.create') }}" class="inline-flex items-center px-4 py-2 bg-[#e63012] hover:bg-red-600 text-white text-sm font-semibold rounded-lg transition-colors">
                    + New Request
                </a>
            </div>
        </div>

        <!-- Search bar -->
        <form method="GET" action="{{ route('client.file-requests.index') }}" class="mb-6 flex gap-3">
            <input type="hidden" name="view" :value="view">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search make, model, request #..."
                class="flex-1 bg-[#1e293b] border border-white/10 text-white placeholder-slate-500 rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-[#e63012]">
            <select name="status" onchange="this.form.submit()"
                class="bg-[#1e293b] border border-white/10 text-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#e63012]">
                <option value="">All Statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </form>

        @if ($fileRequests->isEmpty())
            <div class="bg-[#1e293b] border border-gray-700/50 rounded-xl p-8 text-center text-slate-500 text-sm">
                No file requests found.
            </div>
        @else

        {{-- Board view --}}
        <div x-show="view === 'board'" x-cloak class="overflow-x-auto pb-4">
            <div class="flex gap-4 min-w-max">
                @foreach ($statuses as $status)
                    @php $grouped = $fileRequests instanceof \Illuminate\Pagination\LengthAwarePaginator
                        ? $fileRequests->getCollection()->where('status', $status)
                        : $fileRequests->where('status', $status); @endphp
                    <div class="w-72 flex-shrink-0 bg-[#1e293b] border border-white/5 rounded-xl p-3">
                        <div class="flex items-center justify-between mb-3 px-1">
                            <div class="flex items-center gap-2">
                                <x-status-badge :status="$status->label()" :colour="$status->colour()" />
                            </div>
                            <span class="text-xs font-medium bg-white/5 text-slate-400 rounded-full px-2 py-0.5">{{ $grouped->count() }}</span>
                        </div>
                        <div class="space-y-2">
                            @forelse ($grouped as $fileRequest)
                                <a href="{{ route('client.file-requests.show', $fileRequest) }}"
                                    class="block bg-[#0f172a] border border-white/5 rounded-lg p-3 hover:border-[#e63012]/40 transition-colors">
                                    <div class="flex items-center gap-2 mb-2">
                                        <x-make-logo :make="$fileRequest->make" size="w-6 h-6" />
                                        <span class="text-sm font-medium text-white truncate">{{ $fileRequest->make }} {{ $fileRequest->model }}</span>
                                    </div>
                                    <p class="text-xs text-slate-500">{{ $fileRequest->request_number_formatted }}</p>
                                    <p class="text-xs text-slate-600 mt-1">{{ $fileRequest->created_at->format('d/m/Y') }}</p>
                                </a>
                            @empty
                                <p class="text-xs text-slate-600 text-center py-4">No requests</p>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- List view --}}
        <div x-show="view === 'list'" x-cloak>
            <div class="bg-[#1e293b] border border-gray-700/50 rounded-xl overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-white/5">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Vehicle</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Request #</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Submitted</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach ($fileRequests as $fileRequest)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <x-make-logo :make="$fileRequest->make" size="w-8 h-8" />
                                        <div>
                                            <p class="text-sm font-medium text-white">{{ $fileRequest->make }} {{ $fileRequest->model }}</p>
                                            <p class="text-xs text-slate-500">{{ $fileRequest->year }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-300">{{ $fileRequest->request_number_formatted }}</td>
                                <td class="px-4 py-3">
                                    <x-status-badge :status="$fileRequest->status->label()" :colour="$fileRequest->status->colour()" />
                                </td>
                                <td class="px-4 py-3 text-slate-400 text-xs">{{ $fileRequest->created_at->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('client.file-requests.show', $fileRequest) }}" class="text-xs text-[#e63012] hover:text-red-400">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($fileRequests instanceof \Illuminate\Pagination\LengthAwarePaginator && $fileRequests->hasPages())
                <div class="mt-4">{{ $fileRequests->links() }}</div>
            @endif
        </div>

        @endif
    </div>
</x-layouts.client>
