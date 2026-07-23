<x-layouts.client>

    <!-- Out-of-office / front-page notices -->
    @foreach ($frontPageNotices as $notice)
        <div class="mb-6 rounded-xl border border-amber-500/40 bg-amber-500/10 px-5 py-4 flex items-start gap-4">
            <svg class="w-6 h-6 text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            <div class="flex-1">
                <p class="text-base font-semibold text-amber-300">{{ $notice->title }}</p>
                @if ($notice->body)
                    <p class="text-sm text-amber-200 mt-1 whitespace-pre-line">{{ $notice->body }}</p>
                @endif
                @if ($notice->show_until)
                    <p class="text-xs font-medium text-amber-300 mt-2">Until {{ $notice->show_until->format('j M Y') }}</p>
                @endif
            </div>
        </div>
    @endforeach

    <!-- Welcome banner -->
    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div class="flex-1">
            <h1 class="text-2xl font-bold text-white">Welcome back, {{ auth()->user()->first_name }}</h1>
            <p class="text-slate-400 mt-1 text-sm">Here's what's happening with your account today.</p>
        </div>
        <div class="flex items-center gap-3">
            <!-- Business Status -->
            <div class="bg-[#1e293b] border border-white/10 rounded-xl px-4 py-2.5 text-sm">
                <p class="text-slate-500 text-xs font-medium mb-1">Business Status</p>
                <x-status-badge :status="$portalStatus?->status->label() ?? 'Available'" :colour="$portalStatus?->status->colour() ?? 'green'" />
            </div>
            <a href="{{ route('client.upload.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand hover:bg-brand-dark text-white text-sm font-semibold rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Upload New File
            </a>
        </div>
    </div>

    <!-- Stat cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Pending Files -->
        <div class="bg-[#1e293b] border border-white/5 rounded-xl p-5">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Pending Files</p>
                <p class="text-3xl font-bold text-white mt-2">{{ $stats['pending'] }}</p>
                @if ($deltas['pending_yesterday'] > 0)
                    <p class="text-xs text-brand mt-1 font-medium">+{{ $deltas['pending_yesterday'] }} from yesterday</p>
                @else
                    <p class="text-xs text-slate-600 mt-1">No new yesterday</p>
                @endif
            </div>
        </div>

        <!-- In Progress -->
        <div class="bg-[#1e293b] border border-white/5 rounded-xl p-5">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">In Progress</p>
                <p class="text-3xl font-bold text-white mt-2">{{ $stats['in_progress'] }}</p>
                @if ($deltas['in_progress_today'] > 0)
                    <p class="text-xs text-brand mt-1 font-medium">+{{ $deltas['in_progress_today'] }} from yesterday</p>
                @else
                    <p class="text-xs text-slate-600 mt-1">No change today</p>
                @endif
            </div>
        </div>

        <!-- Completed This Year -->
        <div class="bg-[#1e293b] border border-white/5 rounded-xl p-5">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Completed (This Year)</p>
                <p class="text-3xl font-bold text-white mt-2">{{ $stats['completed_this_year'] }}</p>
                @if ($deltas['completed_this_month'] > 0)
                    <p class="text-xs text-green-400 mt-1 font-medium">+{{ $deltas['completed_this_month'] }} this month</p>
                @else
                    <p class="text-xs text-slate-600 mt-1">None this month</p>
                @endif
            </div>
        </div>

        <!-- Credit Balance -->
        <div class="bg-[#1e293b] border border-white/5 rounded-xl p-5">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Credit Balance</p>
                <p class="text-3xl font-bold text-white mt-2">{{ number_format($stats['file_balance']) }}</p>
                <p class="text-xs text-slate-500 mt-1">File Credits</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left / centre: chart + requests -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Spend chart -->
            <div class="bg-[#1e293b] border border-white/5 rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-slate-300">Spend Over Time</h2>
                    <span class="text-xs bg-white/5 border border-white/10 text-slate-400 rounded-lg px-3 py-1">This Year</span>
                </div>
                <div class="relative h-48">
                    <canvas id="spendChart"></canvas>
                </div>
            </div>

            <!-- Recent file requests -->
            <div class="bg-[#1e293b] border border-white/5 rounded-xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-white/5">
                    <h2 class="text-sm font-semibold text-slate-300">Recent File Requests</h2>
                    <a href="{{ route('client.file-requests.index') }}" class="text-xs text-brand hover:text-red-400">View all &rarr;</a>
                </div>

                @if ($recentFileRequests->isEmpty())
                    <div class="px-6 py-8 text-sm text-slate-500 text-center">No file requests yet.</div>
                @else
                    <div class="divide-y divide-white/5">
                        @foreach ($recentFileRequests as $fileRequest)
                            <div class="flex items-center gap-4 px-6 py-4 hover:bg-white/[0.02] transition-colors">
                                <x-status-badge :status="$fileRequest->status->label()" :colour="$fileRequest->status->colour()" />
                                <x-make-logo :make="$fileRequest->make" size="w-9 h-9" />
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-white truncate">{{ $fileRequest->make }} {{ $fileRequest->model }}</p>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        @if ($fileRequest->fileStage) {{ $fileRequest->fileStage->name }} &bull; @endif
                                        {{ $fileRequest->request_number_formatted }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-4 flex-shrink-0">
                                    <span class="text-xs text-slate-500">{{ $fileRequest->created_at->diffForHumans() }}</span>
                                    <a href="{{ route('client.file-requests.show', $fileRequest) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 bg-white/5 hover:bg-white/10 border border-white/10 text-slate-300 hover:text-white text-xs font-medium rounded-lg transition-colors">
                                        Open
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Right panel -->
        <div class="space-y-4">

            <!-- Notices -->
            @if ($notices->isNotEmpty())
                <div class="bg-[#1e293b] border border-white/5 rounded-xl p-5">
                    <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-3">Notices</h3>
                    <div class="space-y-3">
                        @foreach ($notices as $notice)
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full bg-green-500/10 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-slate-200">{{ $notice->title }}</p>
                                    <p class="text-xs text-slate-500 mt-0.5 line-clamp-2">{{ $notice->body }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Account Summary -->
            @if ($dealer)
                <div class="bg-[#1e293b] border border-white/5 rounded-xl p-5">
                    <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-4">Account Summary</h3>

                    <div class="space-y-4">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm text-slate-400">File Credits</span>
                                <a href="{{ route('client.credits.file') }}"
                                   class="text-xs bg-brand hover:bg-brand-dark text-white px-3 py-1 rounded-lg font-medium transition-colors">
                                    Top Up
                                </a>
                            </div>
                            <p class="text-2xl font-bold text-white">{{ number_format($stats['file_balance']) }}</p>
                        </div>

                        <div class="border-t border-white/5 pt-4">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm text-slate-400">EVC Credits</span>
                                <a href="{{ route('client.credits.evc') }}"
                                   class="text-xs bg-brand hover:bg-brand-dark text-white px-3 py-1 rounded-lg font-medium transition-colors">
                                    Buy EVC Credits
                                </a>
                            </div>
                            <p class="text-2xl font-bold text-white">{{ number_format($stats['evc_balance']) }}</p>
                            @if (filled($dealer?->evc_number))
                                <p class="mt-1 text-xs text-slate-500">EVC Number: <span class="text-slate-300">{{ $dealer->evc_number }}</span></p>
                            @else
                                <a href="{{ route('client.settings.index') }}" class="mt-2 inline-block text-xs text-amber-400 hover:text-amber-300 hover:underline">
                                    Link your EVC number to purchase EVC credits &rarr;
                                </a>
                            @endif
                        </div>

                        <div class="border-t border-white/5 pt-4">
                            <p class="text-sm text-slate-400 mb-1">Total Spent (This Year)</p>
                            <p class="text-2xl font-bold text-white">£{{ number_format($totalSpentThisYear, 2) }}</p>
                        </div>

                        <a href="{{ route('client.invoices.index') }}"
                           class="flex items-center justify-center gap-2 w-full mt-2 py-2.5 bg-white/5 hover:bg-white/10 border border-white/10 text-slate-300 hover:text-white text-xs font-medium rounded-lg transition-colors">
                            View All Transactions
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                        </a>
                    </div>
                </div>
            @endif

            <!-- Portal Status -->
            <div class="bg-[#1e293b] border border-white/5 rounded-xl p-5">
                <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-3">Portal Status</h3>
                <x-status-badge :status="$portalStatus?->status->label() ?? 'Available'" :colour="$portalStatus?->status->colour() ?? 'green'" />
                @if ($todayHours && $todayHours->is_open)
                    <p class="text-xs text-slate-500 mt-3">
                        Open today
                        {{ \Illuminate\Support\Carbon::parse($todayHours->open_time)->format('H:i') }} –
                        {{ \Illuminate\Support\Carbon::parse($todayHours->close_time)->format('H:i') }}
                    </p>
                @else
                    <p class="text-xs text-slate-500 mt-3">Closed today</p>
                @endif
            </div>
        </div>
    </div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('spendChart');
        if (!ctx) return;

        const labels = @json($spendLabels);
        const data   = @json($spendData);

        const brandColour = '{{ \App\Models\Setting::brandColour() }}';
        const hexToRgba = (hex, alpha) => {
            const h = hex.replace('#', '');
            const full = h.length === 3 ? h.split('').map(c => c + c).join('') : h;
            const r = parseInt(full.substring(0, 2), 16);
            const g = parseInt(full.substring(2, 4), 16);
            const b = parseInt(full.substring(4, 6), 16);
            return `rgba(${r}, ${g}, ${b}, ${alpha})`;
        };

        const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 200);
        gradient.addColorStop(0, hexToRgba(brandColour, 0.25));
        gradient.addColorStop(1, hexToRgba(brandColour, 0));

        new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    data,
                    borderColor: brandColour,
                    backgroundColor: gradient,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointBackgroundColor: brandColour,
                    pointHoverRadius: 5,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        borderColor: 'rgba(255,255,255,0.1)',
                        borderWidth: 1,
                        titleColor: '#94a3b8',
                        bodyColor: '#f1f5f9',
                        callbacks: { label: ctx => '£' + ctx.parsed.y.toLocaleString('en-GB', { minimumFractionDigits: 2 }) },
                    },
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(255,255,255,0.04)' },
                        ticks: { color: '#64748b', font: { size: 11 } },
                    },
                    y: {
                        grid: { color: 'rgba(255,255,255,0.04)' },
                        ticks: {
                            color: '#64748b',
                            font: { size: 11 },
                            callback: v => '£' + v.toLocaleString(),
                        },
                        beginAtZero: true,
                    },
                },
            },
        });
    });
</script>
</x-layouts.client>
