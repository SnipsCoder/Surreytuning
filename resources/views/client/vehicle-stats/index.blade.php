<x-layouts.client>
    <x-page-header title="Vehicle Stats" subtitle="Performance figures by make/model" />

    <form method="GET" action="{{ route('client.vehicle-stats.index') }}" class="grid grid-cols-4 gap-4 mb-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Make</label>
            <input type="text" name="make" value="{{ request('make') }}"
                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Model</label>
            <input type="text" name="model" value="{{ request('model') }}"
                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fuel</label>
            <select name="fuel"
                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm">
                <option value="">All</option>
                @foreach (\App\Enums\FuelType::cases() as $fuel)
                    <option value="{{ $fuel->value }}" @selected(request('fuel') === $fuel->value)>{{ $fuel->label() }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-end">
            <button type="submit" class="px-4 py-2 rounded-md bg-gray-700 text-white text-sm font-medium hover:bg-gray-600">
                Filter
            </button>
        </div>
    </form>

    <x-data-table :headers="['Make', 'Model', 'Year Range', 'Engine', 'Fuel', 'Stage', 'BHP Before/After', 'Torque Before/After']">
        @forelse ($vehicleStats as $vehicleStat)
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/60">
                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $vehicleStat->make }}</td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $vehicleStat->model }}</td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $vehicleStat->year_from }} &ndash; {{ $vehicleStat->year_to }}</td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $vehicleStat->engine }}</td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $vehicleStat->fuel->label() }}</td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $vehicleStat->stage }}</td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $vehicleStat->bhp_before }} / {{ $vehicleStat->bhp_after }}</td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $vehicleStat->torque_before_nm }} / {{ $vehicleStat->torque_after_nm }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No vehicle stats found.</td>
            </tr>
        @endforelse
    </x-data-table>

    <div class="mt-4">
        {{ $vehicleStats->links() }}
    </div>
</x-layouts.client>
