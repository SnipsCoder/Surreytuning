<x-layouts.owner>
    <x-page-header title="Reports" subtitle="Business performance and workload insights" />

    @php
        $formatMoney = fn ($v) => '£' . number_format((float) $v, 2);

        if ($avgTurnaroundHours === null) {
            $turnaround = '—';
        } elseif ($avgTurnaroundHours < 48) {
            $turnaround = number_format($avgTurnaroundHours, 1) . ' hrs';
        } else {
            $turnaround = number_format($avgTurnaroundHours / 24, 1) . ' days';
        }
    @endphp

    <!-- Period filter -->
    <div class="flex flex-wrap items-center gap-2 mb-6">
        <span class="text-sm text-slate-400 mr-1">Period:</span>
        @foreach ($periods as $value => $label)
            <a href="{{ route('owner.reports.index', ['period' => $value]) }}"
               class="px-3 py-1.5 rounded-lg text-sm font-medium border transition-colors
                   {{ $period === (string) $value
                       ? 'bg-brand/15 text-brand border-brand/30'
                       : 'text-slate-400 border-transparent hover:text-white hover:bg-white/5' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <!-- Headline metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
        <x-stat-card label="Revenue (paid invoices)" :value="$formatMoney($revenue)" colour="green" />
        <x-stat-card label="File Requests Received" :value="number_format($filesReceived)" colour="blue" />
        <x-stat-card label="Avg Turnaround" :value="$turnaround" colour="yellow" />
        <x-stat-card label="Credits Sold" :value="$formatMoney($creditsSold)" colour="red" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-stat-card label="Files Completed" :value="number_format($filesCompleted)" colour="green" />
        <x-stat-card label="File Credits Sold" :value="$formatMoney($fileCreditsSold)" colour="gray" />
        <x-stat-card label="EVC Credits Sold" :value="$formatMoney($evcCreditsSold)" colour="gray" />
        <x-stat-card label="Product Revenue" :value="$formatMoney($productRevenue)" colour="gray" />
    </div>

    <!-- Daily file volume trend -->
    <div class="bg-[#1e293b] border border-gray-700/50 rounded-xl p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-white">File Requests per Day</h2>
            <span class="text-sm text-slate-400">Last {{ $trendDays }} days</span>
        </div>
        @php $totalTrend = collect($dailyVolume)->sum('count'); @endphp
        @if ($totalTrend === 0)
            <p class="text-sm text-slate-400 py-8 text-center">No file requests in this window.</p>
        @else
            <div class="flex items-end gap-1 h-40">
                @foreach ($dailyVolume as $day)
                    @php $heightPct = round(($day['count'] / $dailyPeak) * 100); @endphp
                    <div class="flex-1 flex flex-col justify-end items-center h-full group relative">
                        <div class="w-full rounded-t {{ $day['count'] > 0 ? 'bg-brand/70 group-hover:bg-brand' : 'bg-white/5' }} transition-colors"
                             style="height: {{ max($heightPct, $day['count'] > 0 ? 4 : 1) }}%"></div>
                        <div class="absolute bottom-full mb-1 hidden group-hover:block whitespace-nowrap px-2 py-1 rounded bg-black/80 text-white text-xs z-10">
                            {{ $day['date']->format('d M') }}: {{ $day['count'] }}
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="flex justify-between mt-2 text-[11px] text-slate-500">
                <span>{{ collect($dailyVolume)->first()['date']->format('d M') }}</span>
                <span>{{ collect($dailyVolume)->last()['date']->format('d M') }}</span>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
        <!-- Top dealers -->
        <div class="bg-[#1e293b] border border-gray-700/50 rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-700/50">
                <h2 class="text-lg font-semibold text-white">Top Dealers <span class="text-sm font-normal text-slate-400">{{ $periodLabel }}</span></h2>
            </div>
            <ul class="divide-y divide-gray-700/50">
                @forelse ($topDealers as $dealer)
                    <li>
                        <a href="{{ route('dealers.show', $dealer) }}" class="flex items-center gap-3 px-6 py-3 hover:bg-white/5">
                            <span class="flex items-center justify-center w-8 h-8 rounded-md bg-brand text-white text-sm font-semibold shrink-0">
                                {{ $loop->iteration }}
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-medium text-white truncate">{{ $dealer->company_name }}</span>
                            </span>
                            <span class="text-sm text-slate-400">{{ $dealer->file_requests_count }} {{ \Illuminate\Support\Str::plural('file', $dealer->file_requests_count) }}</span>
                        </a>
                    </li>
                @empty
                    <li class="px-6 py-4 text-sm text-slate-400">No dealer activity in this period.</li>
                @endforelse
            </ul>
        </div>

        <!-- Busiest vehicle makes -->
        <div class="bg-[#1e293b] border border-gray-700/50 rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-700/50">
                <h2 class="text-lg font-semibold text-white">Busiest Vehicle Makes <span class="text-sm font-normal text-slate-400">{{ $periodLabel }}</span></h2>
            </div>
            @php $makePeak = max(1, (int) collect($topMakes)->max('total')); @endphp
            <ul class="divide-y divide-gray-700/50">
                @forelse ($topMakes as $make)
                    <li class="px-6 py-3">
                        <div class="flex items-center justify-between text-sm mb-1.5">
                            <span class="font-medium text-white">{{ $make->make }}</span>
                            <span class="text-slate-400">{{ $make->total }}</span>
                        </div>
                        <div class="h-1.5 rounded-full bg-white/5 overflow-hidden">
                            <div class="h-full rounded-full bg-brand" style="width: {{ round(($make->total / $makePeak) * 100) }}%"></div>
                        </div>
                    </li>
                @empty
                    <li class="px-6 py-4 text-sm text-slate-400">No vehicle data in this period.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <!-- Status breakdown -->
    <div class="bg-[#1e293b] border border-gray-700/50 rounded-xl overflow-hidden mt-6">
        <div class="px-6 py-4 border-b border-gray-700/50">
            <h2 class="text-lg font-semibold text-white">File Request Status Breakdown <span class="text-sm font-normal text-slate-400">{{ $periodLabel }}</span></h2>
        </div>
        <div class="p-6">
            @if ($statusBreakdown->isEmpty())
                <p class="text-sm text-slate-400">No file requests in this period.</p>
            @else
                <div class="flex flex-wrap gap-3">
                    @foreach ($statusBreakdown as $row)
                        <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-white/5">
                            <x-status-badge :status="$row['status']->label()" :colour="$row['status']->colour()" />
                            <span class="text-lg font-semibold text-white">{{ $row['count'] }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-layouts.owner>
