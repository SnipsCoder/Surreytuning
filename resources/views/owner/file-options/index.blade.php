<x-layouts.owner>
    <x-page-header title="File Options" subtitle="Add-on options available for file requests">
        <button type="button" x-on:click="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-file-option' }))"
            class="px-4 py-2 rounded-md bg-[#e63012] text-white text-sm font-medium hover:bg-[#c92a0f]">
            Add File Option
        </button>
    </x-page-header>

    <x-data-table :headers="['Name', 'File Stage', 'Price (Net)', 'Required', 'Active', '']">
        @forelse ($fileOptions as $fileOption)
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/60">
                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $fileOption->name }}</td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $fileOption->fileStage?->name }}</td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">£{{ number_format((float) $fileOption->price_net, 2) }}</td>
                <td class="px-4 py-3 text-sm">
                    @if ($fileOption->is_required)
                        <x-status-badge status="Required" colour="bg-yellow-100 text-yellow-800" />
                    @else
                        <x-status-badge status="Optional" colour="bg-gray-100 text-gray-800" />
                    @endif
                </td>
                <td class="px-4 py-3 text-sm">
                    @if ($fileOption->is_active)
                        <x-status-badge status="Active" colour="bg-green-100 text-green-800" />
                    @else
                        <x-status-badge status="Inactive" colour="bg-gray-100 text-gray-800" />
                    @endif
                </td>
                <td class="px-4 py-3 text-sm text-right space-x-3">
                    <button type="button" x-on:click="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-file-option-{{ $fileOption->id }}' }))"
                        class="text-indigo-600 dark:text-indigo-400 hover:underline">Edit</button>
                    <form method="POST" action="{{ route('file-options.destroy', $fileOption) }}" class="inline" onsubmit="return confirm('Delete this file option?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">Delete</button>
                    </form>
                </td>
            </tr>

            <x-modal id="edit-file-option-{{ $fileOption->id }}" title="Edit File Option">
                <form method="POST" action="{{ route('file-options.update', $fileOption) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    @include('owner.file-options._form', ['fileOption' => $fileOption, 'fileStages' => $fileStages])
                </form>
            </x-modal>
        @empty
            <tr>
                <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No file options found.</td>
            </tr>
        @endforelse
    </x-data-table>

    <x-modal id="create-file-option" title="Add File Option">
        <form method="POST" action="{{ route('file-options.store') }}" class="space-y-4">
            @csrf
            @include('owner.file-options._form', ['fileOption' => null, 'fileStages' => $fileStages])
        </form>
    </x-modal>
</x-layouts.owner>
