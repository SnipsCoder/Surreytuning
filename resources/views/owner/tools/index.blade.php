<x-layouts.owner>
    <x-page-header title="Tuning Tools" subtitle="Tools used to read/write files">
        <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-tool' }))"
            class="px-4 py-2 rounded-md bg-brand text-white text-sm font-medium hover:bg-[#c92a0f]">
            Add Tool
        </button>
    </x-page-header>

    <x-data-table :headers="['Name', 'Category', 'Sort Order', 'Active', '']">
        @forelse ($tuningTools as $tool)
            <tr class="hover:bg-gray-50 dark:hover:bg-[#1a1a1a]/60">
                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $tool->name }}</td>
                <td class="px-4 py-3 text-sm">
                    <x-status-badge :status="$tool->category->label()" colour="bg-blue-100 text-blue-800" />
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $tool->sort_order }}</td>
                <td class="px-4 py-3 text-sm">
                    @if ($tool->is_active)
                        <x-status-badge status="Active" colour="bg-green-100 text-green-800" />
                    @else
                        <x-status-badge status="Inactive" colour="bg-gray-100 text-gray-800" />
                    @endif
                </td>
                <td class="px-4 py-3 text-sm text-right space-x-3">
                    <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-tool-{{ $tool->id }}' }))"
                        class="text-indigo-600 dark:text-indigo-400 hover:underline">Edit</button>
                    <form method="POST" action="{{ route('tools.destroy', $tool) }}" class="inline" onsubmit="return confirm('Delete this tool?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">Delete</button>
                    </form>
                </td>
            </tr>

            <x-modal id="edit-tool-{{ $tool->id }}" title="Edit Tool">
                <form method="POST" action="{{ route('tools.update', $tool) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    @include('owner.tools._form', ['tool' => $tool])
                </form>
            </x-modal>
        @empty
            <tr>
                <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No tools found.</td>
            </tr>
        @endforelse
    </x-data-table>

    <x-modal id="create-tool" title="Add Tool">
        <form method="POST" action="{{ route('tools.store') }}" class="space-y-4">
            @csrf
            @include('owner.tools._form', ['tool' => null])
        </form>
    </x-modal>
</x-layouts.owner>
