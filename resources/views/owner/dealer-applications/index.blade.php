<x-layouts.owner>
    <x-page-header title="Dealer Applications" subtitle="Review and approve new dealer signups" />

    <form method="GET" action="{{ route('dealer-applications.index') }}" class="mb-6 flex flex-wrap items-center gap-3">
        <select name="status" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm shadow-sm">
            <option value="">All statuses</option>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>

        <button type="submit" class="px-4 py-2 rounded-md bg-gray-900 dark:bg-gray-700 text-white text-sm font-medium hover:bg-gray-800 dark:hover:bg-gray-600">
            Filter
        </button>

        @if (request('status'))
            <a href="{{ route('dealer-applications.index') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:underline">Clear</a>
        @endif
    </form>

    <x-data-table :headers="['Company', 'Contact', 'Email', 'Status', 'Applied', '']">
        @forelse ($applications as $application)
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/60">
                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $application->company_name }}</td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $application->contact_name }}</td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $application->email }}</td>
                <td class="px-4 py-3 text-sm">
                    <x-status-badge :status="$application->status->label()" :colour="$application->status->colour()" />
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $application->created_at->format('d M Y') }}</td>
                <td class="px-4 py-3 text-sm text-right">
                    <a href="{{ route('dealer-applications.show', $application) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Review</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No applications found.</td>
            </tr>
        @endforelse
    </x-data-table>

    <div class="mt-4">
        {{ $applications->links() }}
    </div>
</x-layouts.owner>
