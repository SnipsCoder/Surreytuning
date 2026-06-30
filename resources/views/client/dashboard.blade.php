<x-layouts.client>
    <x-slot name="head">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js" defer></script>
    </x-slot>

    <!-- Welcome banner -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Welcome back, {{ auth()->user()->first_name }}</h1>
        <p class="text-slate-400 mt-1 text-sm">Here's what's happening with your account.</p>
    </div>

    <!-- Stat cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-stat-card label="Pending"            :value="$stats['pending']"             colour="yellow" />
        <x-stat-card label="In Progress"        :value="$stats['in_progress']"         colour="blue" />
        <x-stat-card label="Completed This Year" :value="$stats['completed_this_year']" colour="green" />
        <x-stat-card label="Total Spent"        :value="'£'.number_format($totalSpent, 2)" colour="red" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left / centre: chart + requests -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Spend chart -->
            <div class="bg-[#1e293b] border border-gray-700/50 rounded-xl p-6">
                <h2 class="text-sm font-semibold text-slate-300 mb-4">Spend — Last 12 Months</h2>
                <div class="relative h-48">
                    <canvas id="spendChart"></canvas>
                </div>
            </div>

            <!-- Recent file requests -->
            <div class="bg-[#1e293b] border border-gray-700/50 rounded-xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-white/5">
                    <h2 class="text-sm font-semibold text-slate-300">Recent File Requests</h2>
                    <a href="{{ route('client.file-requests.index') }}" class="text-xs text-[#e63012] hover:text-red-400">View all &rarr;</a>
                </div>

                @if ($recentFileRequests->isEmpty())
                    <div class="px-6 py-8 text-sm text-slate-500 text-center">No file requests yet.</div>
                @else
                    <div class="divide-y divide-white/5">
                        @foreach ($recentFileRequests as $fileRequest)
                            <div class="flex items-center gap-4 px-6 py-4 hover:bg-white/[0.02] transition-colors">
                                <x-make-logo :make="$fileRequest->make" size="w-9 h-9" />
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-white truncate">
                                        {{ $fileRequest->make }} {{ $fileRequest->model }}
                                        <span class="text-slate-500">({{ $fileRequest->year }})</span>
                                    </p>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        {{ $fileRequest->request_number_formatted }}
                                        @if ($fileRequest->fileStage)
                                            &bull; {{ $fileRequest->fileStage->name }}
                                        @endif
                                    </p>
                                </div>
                                <div class="flex items-center gap-3 flex-shrink-0">
                                    <x-status-badge :status="$fileRequest->status->label()" :colour="$fileRequest->status->colour()" />
                                    <span class="text-xs text-slate-500">{{ $fileRequest->created_at->format('d/m/Y') }}</span>
                                    <a href="{{ route('client.file-requests.show', $fileRequest) }}" class="text-xs text-[#e63012] hover:text-red-400">View</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Right panel -->
        <div class="space-y-4">

            <!-- Portal status -->
            <div class="bg-[#1e293b] border border-gray-700/50 rounded-xl p-5">
                <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-3">Portal Status</h3>
                <x-status-badge :status="$portalStatus->status->label()" :colour="$portalStatus->status->colour()" />
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

            <!-- Credit balances -->
            @if ($dealer)
                <div class="bg-[#1e293b] border border-gray-700/50 rounded-xl p-5">
                    <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-3">Credit Balances</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-400">Slave Credits</span>
                            <span class="text-sm font-semibold text-white">{{ number_format($stats['slave_balance']) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-400">EVC Credits</span>
                            <span class="text-sm font-semibold text-white">{{ number_format($stats['evc_balance']) }}</span>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Notices -->
            <div class="bg-[#1e293b] border border-gray-700/50 rounded-xl p-5">
                <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-3">Notices</h3>
                @if ($notices->isEmpty())
                    <p class="text-sm text-slate-500">No current notices.</p>
                @else
                    <div class="space-y-4">
                        @foreach ($notices as $notice)
                            <div>
                                <p class="text-sm font-medium text-slate-200">{{ $notice->title }}</p>
                                <p class="text-xs text-slate-500 mt-1">{{ $notice->body }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('spendChart');
            if (!ctx) return;

            const labels = @json($spendLabels);
            const data   = @json($spendData);

            const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 200);
            gradient.addColorStop(0, 'rgba(230, 48, 18, 0.3)');
            gradient.addColorStop(1, 'rgba(230, 48, 18, 0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        data,
                        borderColor: '#e63012',
                        backgroundColor: gradient,
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 3,
                        pointBackgroundColor: '#e63012',
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: {
                            grid: { color: 'rgba(255,255,255,0.05)' },
                            ticks: { color: '#94a3b8', font: { size: 11 } },
                        },
                        y: {
                            grid: { color: 'rgba(255,255,255,0.05)' },
                            ticks: {
                                color: '#94a3b8',
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
