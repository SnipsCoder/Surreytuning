<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Make</label>
        <input type="text" name="make" value="{{ old('make', $vehicleStat?->make) }}" required
            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Model</label>
        <input type="text" name="model" value="{{ old('model', $vehicleStat?->model) }}" required
            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm">
    </div>
</div>

<div class="grid grid-cols-3 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Year From</label>
        <input type="number" name="year_from" value="{{ old('year_from', $vehicleStat?->year_from) }}" required
            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Year To</label>
        <input type="number" name="year_to" value="{{ old('year_to', $vehicleStat?->year_to) }}" required
            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Engine</label>
        <input type="text" name="engine" value="{{ old('engine', $vehicleStat?->engine) }}" required
            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm">
    </div>
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fuel</label>
        <select name="fuel" required
            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm">
            @foreach (\App\Enums\FuelType::cases() as $fuel)
                <option value="{{ $fuel->value }}" @selected(old('fuel', $vehicleStat?->fuel?->value) === $fuel->value)>
                    {{ $fuel->label() }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Stage</label>
        <input type="number" name="stage" min="1" max="5" value="{{ old('stage', $vehicleStat?->stage) }}" required
            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm">
    </div>
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">BHP Before</label>
        <input type="number" step="0.01" name="bhp_before" value="{{ old('bhp_before', $vehicleStat?->bhp_before) }}" required
            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">BHP After</label>
        <input type="number" step="0.01" name="bhp_after" value="{{ old('bhp_after', $vehicleStat?->bhp_after) }}" required
            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm">
    </div>
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Torque Before (Nm)</label>
        <input type="number" step="0.01" name="torque_before_nm" value="{{ old('torque_before_nm', $vehicleStat?->torque_before_nm) }}" required
            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Torque After (Nm)</label>
        <input type="number" step="0.01" name="torque_after_nm" value="{{ old('torque_after_nm', $vehicleStat?->torque_after_nm) }}" required
            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm">
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
    <textarea name="notes" rows="3"
        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm">{{ old('notes', $vehicleStat?->notes) }}</textarea>
</div>

<div class="flex justify-end gap-3 pt-2">
    <a href="{{ route('vehicle-stats.index') }}" class="px-4 py-2 rounded-md text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
        Cancel
    </a>
    <button type="submit" class="px-4 py-2 rounded-md bg-[#e63012] text-white text-sm font-medium hover:bg-[#c92a0f]">
        Save
    </button>
</div>
