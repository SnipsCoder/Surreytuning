<x-layouts.owner>
    <x-page-header title="Dealers" subtitle="All registered dealer accounts" />

    <form method="GET" action="{{ route('dealers.index') }}" class="mb-6 flex flex-wrap items-center gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search company name..."
            class="flex-1 min-w-[220px] rounded-md border-gray-300 dark:border-[#2a2a2a] dark:bg-[#1a1a1a] dark:text-gray-100 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">

        <select name="status" class="rounded-md border-gray-300 dark:border-[#2a2a2a] dark:bg-[#1a1a1a] dark:text-gray-100 text-sm shadow-sm">
            <option value="">All statuses</option>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>

        <button type="submit" class="px-4 py-2 rounded-md bg-[#0d0d0d] dark:bg-gray-700 text-white text-sm font-medium hover:bg-[#1a1a1a] dark:hover:bg-gray-600">
            Filter
        </button>

        @if (request('search') || request('status'))
            <a href="{{ route('dealers.index') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:underline">Clear</a>
        @endif
    </form>

    <x-data-table :headers="['Company', 'Status', 'File Credits', 'EVC Credits', 'Job Count', 'Joined', '']">
        @forelse ($dealers as $dealer)
            <tr class="hover:bg-gray-50 dark:hover:bg-[#1a1a1a]/60">
                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $dealer->company_name }}</td>
                <td class="px-4 py-3 text-sm">
                    <x-status-badge :status="$dealer->status->label()" :colour="$dealer->status->colour()" />
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ number_format((float) $dealer->file_credit_balance, 2) }}</td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ number_format((float) $dealer->evc_credit_balance, 2) }}</td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $dealer->file_requests_count }}</td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $dealer->created_at->format('d M Y') }}</td>
                <td class="px-4 py-3 text-sm text-right">
                    <a href="{{ route('dealers.show', $dealer) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">View</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No dealers found.</td>
            </tr>
        @endforelse
    </x-data-table>

    <div class="mt-4">
        {{ $dealers->links() }}
    </div>
</x-layouts.owner>
