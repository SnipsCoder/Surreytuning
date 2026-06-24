<x-layouts.owner>
    <x-page-header title="Dashboard" subtitle="Overview of file requests" />

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card label="Open Requests" :value="$openRequestsCount" colour="blue" />
        <x-stat-card label="Pending Action" :value="$pendingActionCount" colour="yellow" />
        <x-stat-card label="Closed (30 days)" :value="$closedThisMonthCount" colour="green" />
        <x-stat-card label="Total Dealers" :value="$dealerCount" colour="gray" />
    </div>
</x-layouts.owner>
