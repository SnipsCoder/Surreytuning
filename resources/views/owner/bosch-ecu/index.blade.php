<x-layouts.owner>
    <x-page-header title="Bosch ECU Search" subtitle="Search Bosch ECU reference data" />

    <form method="GET" action="{{ route('owner.bosch-ecu.index') }}" class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow p-4 mb-6 flex flex-wrap items-end gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Manufacturer Number</label>
            <input type="text" name="manufacturer_number" value="{{ $manufacturerNumber }}" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Car Producer</label>
            <input type="text" name="car_producer" value="{{ $carProducer }}" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm">
        </div>
        <button type="submit" class="px-4 py-2 rounded-md text-sm font-medium text-white bg-[#e63012] hover:bg-[#c8280f]">
            Search
        </button>
        @if ($hasSearched)
            <a href="{{ route('owner.bosch-ecu.index') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:underline">Clear</a>
        @endif
    </form>

    @if (! $hasSearched)
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow p-8 text-center text-sm text-gray-500 dark:text-gray-400">
            Enter a manufacturer number or car producer to search.
        </div>
    @else
        <x-data-table :headers="['Manufacturer Number', 'Model', 'Car Producer']">
            @forelse ($results as $ecu)
                <tr>
                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ $ecu->manufacturer_number }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $ecu->model }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $ecu->car_producer }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="px-6 py-4 text-sm text-center text-gray-500 dark:text-gray-400">No results found.</td>
                </tr>
            @endforelse
        </x-data-table>

        <div class="mt-4">
            {{ $results->links() }}
        </div>
    @endif
</x-layouts.owner>
