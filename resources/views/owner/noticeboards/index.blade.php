<x-layouts.owner>
    <x-page-header title="Noticeboard" subtitle="Notices shown to dealers">
        <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-noticeboard' }))"
            class="px-4 py-2 rounded-md bg-brand text-white text-sm font-medium hover:bg-[#c92a0f]">
            Add Notice
        </button>
    </x-page-header>

    <x-data-table :headers="['Title', 'Priority', 'Show From', 'Show Until', 'Active', 'Created', '']">
        @forelse ($noticeboards as $noticeboard)
            <tr class="hover:bg-gray-50 dark:hover:bg-[#1a1a1a]/60">
                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $noticeboard->title }}</td>
                <td class="px-4 py-3 text-sm">
                    <x-status-badge :status="$noticeboard->priority->label()" colour="bg-blue-100 text-blue-800" />
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $noticeboard->show_from?->format('d/m/Y') ?? '-' }}</td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $noticeboard->show_until?->format('d/m/Y') ?? '-' }}</td>
                <td class="px-4 py-3 text-sm">
                    @if ($noticeboard->is_active)
                        <x-status-badge status="Active" colour="bg-green-100 text-green-800" />
                    @else
                        <x-status-badge status="Inactive" colour="bg-gray-100 text-gray-800" />
                    @endif
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $noticeboard->created_at->format('d/m/Y') }}</td>
                <td class="px-4 py-3 text-sm text-right space-x-3">
                    <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-noticeboard-{{ $noticeboard->id }}' }))"
                        class="text-indigo-600 dark:text-indigo-400 hover:underline">Edit</button>
                    <form method="POST" action="{{ route('noticeboards.destroy', $noticeboard) }}" class="inline" onsubmit="return confirm('Delete this notice?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">Delete</button>
                    </form>
                </td>
            </tr>

            <x-modal id="edit-noticeboard-{{ $noticeboard->id }}" title="Edit Notice">
                <form method="POST" action="{{ route('noticeboards.update', $noticeboard) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    @include('owner.noticeboards._form', ['noticeboard' => $noticeboard])
                </form>
            </x-modal>
        @empty
            <tr>
                <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No notices found.</td>
            </tr>
        @endforelse
    </x-data-table>

    <x-modal id="create-noticeboard" title="Add Notice">
        <form method="POST" action="{{ route('noticeboards.store') }}" class="space-y-4">
            @csrf
            @include('owner.noticeboards._form', ['noticeboard' => null])
        </form>
    </x-modal>
</x-layouts.owner>
