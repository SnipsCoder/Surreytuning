<x-layouts.owner>
    <x-page-header title="WinOLS Bundles" subtitle="Credit bundles dealers can purchase">
        <button type="button" x-on:click="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-winols-bundle' }))"
            class="px-4 py-2 rounded-md bg-[#e63012] text-white text-sm font-medium hover:bg-[#c92a0f]">
            Add Bundle
        </button>
    </x-page-header>

    <x-data-table :headers="['Name', 'Credits', 'Price (Net)', 'Active', '']">
        @forelse ($winolsBundles as $winolsBundle)
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/60">
                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $winolsBundle->name }}</td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $winolsBundle->credits }}</td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">£{{ number_format((float) $winolsBundle->price_net, 2) }}</td>
                <td class="px-4 py-3 text-sm">
                    @if ($winolsBundle->is_active)
                        <x-status-badge status="Active" colour="bg-green-100 text-green-800" />
                    @else
                        <x-status-badge status="Inactive" colour="bg-gray-100 text-gray-800" />
                    @endif
                </td>
                <td class="px-4 py-3 text-sm text-right space-x-3">
                    <button type="button" x-on:click="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-winols-bundle-{{ $winolsBundle->id }}' }))"
                        class="text-indigo-600 dark:text-indigo-400 hover:underline">Edit</button>
                    <form method="POST" action="{{ route('winols-bundles.destroy', $winolsBundle) }}" class="inline" onsubmit="return confirm('Delete this bundle?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">Delete</button>
                    </form>
                </td>
            </tr>

            <x-modal id="edit-winols-bundle-{{ $winolsBundle->id }}" title="Edit Bundle">
                <form method="POST" action="{{ route('winols-bundles.update', $winolsBundle) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    @include('owner.winols-bundles._form', ['winolsBundle' => $winolsBundle])
                </form>
            </x-modal>
        @empty
            <tr>
                <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No bundles found.</td>
            </tr>
        @endforelse
    </x-data-table>

    <x-modal id="create-winols-bundle" title="Add Bundle">
        <form method="POST" action="{{ route('winols-bundles.store') }}" class="space-y-4">
            @csrf
            @include('owner.winols-bundles._form', ['winolsBundle' => null])
        </form>
    </x-modal>
</x-layouts.owner>
