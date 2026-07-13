<x-layouts.owner>
    <x-page-header title="What's New" subtitle="Updates shown to dealers">
        <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-whats-new' }))"
            class="px-4 py-2 rounded-md bg-brand text-white text-sm font-medium hover:bg-[#c92a0f]">
            Add Update
        </button>
    </x-page-header>

    <x-data-table :headers="['Title', 'Version', 'Published', '']">
        @forelse ($whatsNews as $whatsNew)
            <tr class="hover:bg-gray-50 dark:hover:bg-[#1a1a1a]/60">
                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $whatsNew->title }}</td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $whatsNew->version ?? '-' }}</td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $whatsNew->published_at?->format('d/m/Y') ?? '-' }}</td>
                <td class="px-4 py-3 text-sm text-right space-x-3">
                    <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-whats-new-{{ $whatsNew->id }}' }))"
                        class="text-indigo-600 dark:text-indigo-400 hover:underline">Edit</button>
                    <form method="POST" action="{{ route('whats-new.destroy', $whatsNew) }}" class="inline" onsubmit="return confirm('Delete this update?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">Delete</button>
                    </form>
                </td>
            </tr>

            <x-modal id="edit-whats-new-{{ $whatsNew->id }}" title="Edit Update">
                <form method="POST" action="{{ route('whats-new.update', $whatsNew) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    @include('owner.whats-new._form', ['whatsNew' => $whatsNew])
                </form>
            </x-modal>
        @empty
            <tr>
                <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No updates found.</td>
            </tr>
        @endforelse
    </x-data-table>

    <x-modal id="create-whats-new" title="Add Update">
        <form method="POST" action="{{ route('whats-new.store') }}" class="space-y-4">
            @csrf
            @include('owner.whats-new._form', ['whatsNew' => null])
        </form>
    </x-modal>
</x-layouts.owner>
