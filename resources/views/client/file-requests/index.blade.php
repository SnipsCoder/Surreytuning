<x-layouts.client>
    <div x-data="{ view: localStorage.getItem('client-frq-view') || 'board' }" x-init="$watch('view', v => localStorage.setItem('client-frq-view', v))">

        <!-- Header -->
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <h1 class="text-xl font-bold text-white">File Requests</h1>
            <div class="flex items-center gap-3">
                <form method="GET" action="{{ route('client.file-requests.index') }}" class="flex items-center gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search make, model, request #..."
                        class="bg-[#1e293b] border border-white/10 text-white placeholder-slate-500 rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-brand w-56">
                    <button type="submit"
                        class="px-4 py-2 bg-[#1e293b] border border-white/10 text-slate-300 hover:text-white text-sm font-medium rounded-lg transition-colors">
                        Filter
                    </button>
                </form>
                <a href="{{ route('client.upload.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-brand hover:bg-brand-dark text-white text-sm font-semibold rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Request
                </a>
            </div>
        </div>

        <!-- Tab toggle -->
        <div class="flex gap-6 border-b border-white/10 mb-6">
            <button type="button" x-on:click="view = 'board'"
                class="pb-3 text-sm font-medium transition-colors border-b-2 -mb-px"
                :class="view === 'board' ? 'text-white border-brand' : 'text-slate-500 border-transparent hover:text-slate-300'">
                Board View
            </button>
            <button type="button" x-on:click="view = 'list'"
                class="pb-3 text-sm font-medium transition-colors border-b-2 -mb-px"
                :class="view === 'list' ? 'text-white border-brand' : 'text-slate-500 border-transparent hover:text-slate-300'">
                List View
            </button>
        </div>

        @if ($fileRequests->isEmpty())
            <div class="bg-[#1e293b] border border-white/5 rounded-xl p-8 text-center text-slate-500 text-sm">
                No file requests found.
            </div>
        @else

        {{-- Board view --}}
        <div x-show="view === 'board'" x-cloak class="pb-4">
            <div class="grid grid-cols-7 gap-3 items-start">
                @foreach ($statuses as $status)
                    @continue($status === \App\Enums\FileRequestStatus::Void)
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
                    <div class="min-w-0 bg-[#1e293b] border border-white/5 rounded-xl p-3">
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
                                <a href="{{ route('client.file-requests.show', $fileRequest) }}"
                                    class="block bg-[#0f172a] border border-white/5 border-l-4 {{ $borderClass }} rounded-lg p-3 hover:border-white/10 hover:border-l-4 hover:{{ $borderClass }} transition-colors group">
                                    <div class="flex items-start justify-between gap-2 mb-2">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <x-make-logo :make="$fileRequest->make" size="w-7 h-7" />
                                            <span class="text-sm font-semibold text-white truncate">{{ $fileRequest->make }} {{ $fileRequest->model }}</span>
                                        </div>
                                        <button type="button" class="text-slate-600 hover:text-slate-400 transition-colors flex-shrink-0 opacity-0 group-hover:opacity-100" onclick="event.preventDefault()">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm0 7a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm0 7a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z"/></svg>
                                        </button>
                                    </div>
                                    @if ($fileRequest->fileStage)
                                        <p class="text-xs text-slate-500 mb-1.5">{{ $fileRequest->fileStage->name }}</p>
                                    @endif
                                    <div class="flex items-center justify-between mt-2">
                                        <span class="text-xs font-mono text-slate-600">{{ $fileRequest->request_number_formatted }}</span>
                                        <span class="text-xs text-slate-600">{{ $fileRequest->created_at->diffForHumans() }}</span>
                                    </div>
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
            <div class="bg-[#1e293b] border border-white/5 rounded-xl overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-white/5">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Vehicle</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Request #</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Stage</th>
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
                                        <x-make-logo :make="$fileRequest->make" size="w-9 h-9" />
                                        <div>
                                            <p class="text-sm font-semibold text-white">{{ $fileRequest->make }} {{ $fileRequest->model }}</p>
                                            <p class="text-xs text-slate-500">{{ $fileRequest->year }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-300 font-mono text-xs">{{ $fileRequest->request_number_formatted }}</td>
                                <td class="px-4 py-3 text-slate-400 text-xs">{{ $fileRequest->fileStage?->name ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <x-status-badge :status="$fileRequest->status->label()" :colour="$fileRequest->status->colour()" />
                                </td>
                                <td class="px-4 py-3 text-slate-500 text-xs">{{ $fileRequest->created_at->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('client.file-requests.show', $fileRequest) }}"
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
</x-layouts.client>
