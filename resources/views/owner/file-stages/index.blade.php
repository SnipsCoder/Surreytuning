<x-layouts.owner>
    <x-page-header title="File Stages" subtitle="Tuning stages available for file requests">
        <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-file-stage' }))"
            class="px-4 py-2 rounded-md bg-brand text-white text-sm font-medium hover:bg-[#c92a0f]">
            Add File Stage
        </button>
    </x-page-header>

    <x-data-table :headers="['Name', 'Vehicle Type', 'Price (Net)', 'Turnaround', 'Active', '']">
        @forelse ($fileStages as $fileStage)
            <tr class="hover:bg-gray-50 dark:hover:bg-[#1a1a1a]/60">
                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $fileStage->name }}</td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $fileStage->vehicle_type->label() }}</td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">£{{ number_format((float) $fileStage->price_net, 2) }}</td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $fileStage->turnaround_hours !== null ? $fileStage->turnaround_hours . 'h' : '—' }}</td>
                <td class="px-4 py-3 text-sm">
                    @if ($fileStage->is_active)
                        <x-status-badge status="Active" colour="bg-green-100 text-green-800" />
                    @else
                        <x-status-badge status="Inactive" colour="bg-gray-100 text-gray-800" />
                    @endif
                </td>
                <td class="px-4 py-3 text-sm text-right space-x-3">
                    <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-file-stage-{{ $fileStage->id }}' }))"
                        class="text-indigo-600 dark:text-indigo-400 hover:underline">Edit</button>
                    <form method="POST" action="{{ route('file-stages.destroy', $fileStage) }}" class="inline" onsubmit="return confirm('Delete this file stage?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No file stages found.</td>
            </tr>
        @endforelse
    </x-data-table>

    {{-- Edit modals live outside the table: a <div> inside <tbody> is invalid HTML and gets hoisted out by the browser. --}}
    @foreach ($fileStages as $fileStage)
        <x-modal id="edit-file-stage-{{ $fileStage->id }}" title="Edit File Stage">
            <form method="POST" action="{{ route('file-stages.update', $fileStage) }}" class="space-y-4">
                @csrf
                @method('PUT')
                @include('owner.file-stages._form', ['fileStage' => $fileStage])
            </form>
        </x-modal>
    @endforeach

    <x-modal id="create-file-stage" title="Add File Stage">
        <form method="POST" action="{{ route('file-stages.store') }}" class="space-y-4">
            @csrf
            @include('owner.file-stages._form', ['fileStage' => null])
        </form>
    </x-modal>
</x-layouts.owner>
