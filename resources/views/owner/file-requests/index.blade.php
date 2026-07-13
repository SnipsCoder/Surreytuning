<x-layouts.owner>
    <div x-data="{ view: localStorage.getItem('owner-frq-view') || 'board' }"
         x-init="$watch('view', v => localStorage.setItem('owner-frq-view', v))">

        <!-- Header -->
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl font-bold text-white">File Requests</h1>
                <p class="text-sm text-slate-500 mt-0.5">Active tuning file requests across all dealers</p>
            </div>
            <div class="flex items-center gap-2">
                <div class="inline-flex rounded-lg overflow-hidden border border-white/10">
                    <button type="button" x-on:click="view = 'board'"
                        :class="view === 'board' ? 'bg-brand text-white' : 'bg-[#141414] text-slate-400 hover:text-white'"
                        class="px-4 py-1.5 text-sm font-semibold transition-colors">
                        Board
                    </button>
                    <button type="button" x-on:click="view = 'list'"
                        :class="view === 'list' ? 'bg-brand text-white' : 'bg-[#141414] text-slate-400 hover:text-white'"
                        class="px-4 py-1.5 text-sm font-semibold transition-colors border-x border-white/10">
                        List
                    </button>
                    <a href="{{ route('owner.file-requests.archive') }}"
                       class="px-4 py-1.5 text-sm font-semibold bg-[#141414] text-slate-400 hover:text-white transition-colors">
                        Archive
                    </a>
                </div>
            </div>
        </div>

        <!-- Search / filter bar -->
        <form method="GET" action="{{ route('file-requests.index') }}" class="mb-6 flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Search request #, make, model, dealer..."
                class="flex-1 min-w-[220px] bg-[#141414] border border-white/10 text-white placeholder-slate-500 rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-brand">

            <select name="status" onchange="this.form.submit()"
                class="bg-[#141414] border border-white/10 text-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand">
                <option value="">All Statuses</option>
                @foreach ($statuses as $status)
                    @continue(in_array($status, [\App\Enums\FileRequestStatus::Closed, \App\Enums\FileRequestStatus::Void], true))
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>

            <select name="assigned_technician_id" onchange="this.form.submit()"
                class="bg-[#141414] border border-white/10 text-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand">
                <option value="">All Technicians</option>
                @foreach ($technicians as $technician)
                    <option value="{{ $technician->id }}" @selected((string) request('assigned_technician_id') === (string) $technician->id)>{{ $technician->full_name }}</option>
                @endforeach
            </select>

            <button type="submit"
                class="px-4 py-2 bg-[#141414] border border-white/10 text-slate-300 hover:text-white text-sm font-medium rounded-lg transition-colors">
                Filter
            </button>

            @if (request('search') || request('status') || request('assigned_technician_id'))
                <a href="{{ route('file-requests.index') }}" class="text-sm text-slate-500 hover:text-slate-300 self-center">Clear</a>
            @endif
        </form>

        @if ($fileRequests->isEmpty())
            <div class="bg-[#141414] border border-white/5 rounded-xl p-8 text-center text-slate-500 text-sm">
                No file requests found.
            </div>
        @else

        {{-- Board view --}}
        <div x-show="view === 'board'" x-cloak class="overflow-x-auto pb-4">
            <div class="flex gap-4 min-w-max">
                @foreach ($statuses as $status)
                    @continue(in_array($status, [\App\Enums\FileRequestStatus::Closed, \App\Enums\FileRequestStatus::Void], true))
                    @php
                        $grouped = $fileRequests instanceof \Illuminate\Pagination\LengthAwarePaginator
                            ? $fileRequests->getCollection()->where('status', $status)
                            : $fileRequests->where('status', $status);

                        $dotColours = [
                            'blue'   => 'bg-blue-400',
                            'amber'  => 'bg-amber-400',
                            'orange' => 'bg-orange-400',
                            'green'  => 'bg-green-400',
                            'red'    => 'bg-red-400',
                            'slate'  => 'bg-slate-400',
                            'purple' => 'bg-purple-400',
                        ];
                        $dotClass = $dotColours[$status->colour()] ?? 'bg-slate-400';

                        $borderColours = [
                            'blue'   => 'border-l-blue-500',
                            'amber'  => 'border-l-amber-500',
                            'orange' => 'border-l-orange-500',
                            'green'  => 'border-l-green-500',
                            'red'    => 'border-l-red-500',
                            'slate'  => 'border-l-slate-500',
                            'purple' => 'border-l-purple-500',
                        ];
                        $borderClass = $borderColours[$status->colour()] ?? 'border-l-slate-500';
                    @endphp
                    <div class="w-72 flex-shrink-0 bg-[#141414] border border-white/5 rounded-xl p-3">
                        <!-- Column header -->
                        <div class="flex items-center justify-between mb-3 px-1">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full {{ $dotClass }}"></span>
                                <span class="text-sm font-semibold text-slate-200">{{ $status->label() }}</span>
                            </div>
                            <span class="text-xs font-medium bg-white/5 text-slate-400 rounded-full px-2 py-0.5">{{ $grouped->count() }}</span>
                        </div>

                        <div class="space-y-2">
                            @forelse ($grouped as $fileRequest)
                                <a href="{{ route('file-requests.show', $fileRequest) }}"
                                    class="block bg-[#0d0d0d] border border-white/5 border-l-4 {{ $borderClass }} rounded-lg p-3 hover:border-white/10 transition-colors group">
                                    <div class="flex items-start justify-between gap-2 mb-2">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <x-make-logo :make="$fileRequest->make" size="w-7 h-7" />
                                            <span class="text-sm font-semibold text-white truncate">{{ $fileRequest->make }} {{ $fileRequest->model }}</span>
                                        </div>
                                        <button type="button" class="text-slate-600 hover:text-slate-400 transition-colors flex-shrink-0 opacity-0 group-hover:opacity-100" onclick="event.preventDefault()">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm0 7a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm0 7a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z"/></svg>
                                        </button>
                                    </div>
                                    <p class="text-xs text-slate-500 mb-1.5 truncate">{{ $fileRequest->dealer?->company_name ?? '—' }}</p>
                                    <div class="flex items-center justify-between mt-2">
                                        <span class="text-xs font-mono text-slate-600">{{ $fileRequest->request_number_formatted }}</span>
                                        <span class="text-xs text-slate-600">{{ $fileRequest->created_at->diffForHumans() }}</span>
                                    </div>
                                    @if ($fileRequest->assignedTechnician)
                                        <div class="flex items-center gap-1.5 mt-2 pt-2 border-t border-white/5">
                                            <div class="w-4 h-4 rounded-full bg-slate-700 flex items-center justify-center text-[9px] font-bold text-slate-300">
                                                {{ strtoupper(substr($fileRequest->assignedTechnician->first_name ?? '', 0, 1)) }}
                                            </div>
                                            <span class="text-xs text-slate-500 truncate">{{ $fileRequest->assignedTechnician->full_name }}</span>
                                        </div>
                                    @endif
                                </a>
                            @empty
                                <p class="text-xs text-slate-600 text-center py-6">No requests</p>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- List view --}}
        <div x-show="view === 'list'" x-cloak>
            <div class="bg-[#141414] border border-white/5 rounded-xl overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-white/5">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Vehicle</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Request #</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Dealer</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Stage</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Technician</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
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
                                            <p class="text-sm font-semibold text-white">{{ $fileRequest->make }} {{ $fileRequest->model }}</p>
                                            <p class="text-xs text-slate-500">{{ $fileRequest->year }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-300 font-mono text-xs">{{ $fileRequest->request_number_formatted }}</td>
                                <td class="px-4 py-3 text-slate-400 text-xs">{{ $fileRequest->dealer?->company_name ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-400 text-xs">{{ $fileRequest->fileStage?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-400 text-xs">{{ $fileRequest->assignedTechnician?->full_name ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <x-status-badge :status="$fileRequest->status->label()" :colour="$fileRequest->status->colour()" />
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('file-requests.show', $fileRequest) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 bg-white/5 hover:bg-white/10 border border-white/10 text-slate-300 hover:text-white text-xs font-medium rounded-lg transition-colors">
                                        Open
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
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
</x-layouts.owner>
