@php
    $vehicleTypes = \App\Enums\VehicleType::cases();
@endphp

<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
    <input type="text" name="name" value="{{ old('name', $fileStage?->name) }}" required
        class="mt-1 block w-full rounded-md border-gray-300 dark:border-[#2a2a2a] dark:bg-[#0d0d0d] dark:text-gray-100 text-sm shadow-sm">
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
    <textarea name="description" rows="2"
        class="mt-1 block w-full rounded-md border-gray-300 dark:border-[#2a2a2a] dark:bg-[#0d0d0d] dark:text-gray-100 text-sm shadow-sm">{{ old('description', $fileStage?->description) }}</textarea>
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Vehicle Type</label>
        <select name="vehicle_type" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-[#2a2a2a] dark:bg-[#0d0d0d] dark:text-gray-100 text-sm shadow-sm">
            @foreach ($vehicleTypes as $type)
                <option value="{{ $type->value }}" @selected(old('vehicle_type', $fileStage?->vehicle_type?->value) === $type->value)>{{ $type->label() }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Turnaround (hours)</label>
        <input type="number" name="turnaround_hours" min="0" value="{{ old('turnaround_hours', $fileStage?->turnaround_hours) }}" required
            class="mt-1 block w-full rounded-md border-gray-300 dark:border-[#2a2a2a] dark:bg-[#0d0d0d] dark:text-gray-100 text-sm shadow-sm">
    </div>
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Price (Net)</label>
        <input type="number" name="price_net" step="0.01" min="0" value="{{ old('price_net', $fileStage?->price_net) }}" required
            class="mt-1 block w-full rounded-md border-gray-300 dark:border-[#2a2a2a] dark:bg-[#0d0d0d] dark:text-gray-100 text-sm shadow-sm">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sort Order</label>
        <input type="number" name="sort_order" value="{{ old('sort_order', $fileStage?->sort_order ?? 0) }}"
            class="mt-1 block w-full rounded-md border-gray-300 dark:border-[#2a2a2a] dark:bg-[#0d0d0d] dark:text-gray-100 text-sm shadow-sm">
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Image</label>
    @if ($fileStage?->image_path)
        <img src="{{ route('file-stages.image', $fileStage) }}" alt="{{ $fileStage->name }}" class="mt-2 h-16 w-16 object-cover rounded-md">
    @endif
    <input type="file" name="image" accept="image/*"
        class="mt-2 block w-full text-sm text-gray-700 dark:text-gray-300">
</div>

<div class="flex items-center gap-6">
    {{-- VAT toggle is hidden from the UI; carry the stage's current value forward so saving does not flip it. --}}
    <input type="hidden" name="vat_applicable" value="{{ old('vat_applicable', $fileStage?->vat_applicable ?? true) ? 1 : 0 }}">

    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $fileStage?->is_active ?? true))>
        Active
    </label>
</div>

<div class="flex justify-end gap-3 pt-2">
    <button type="button" x-on:click="open = false" class="px-4 py-2 rounded-md text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
        Cancel
    </button>
    <button type="submit" class="px-4 py-2 rounded-md bg-brand text-white text-sm font-medium hover:bg-[#c92a0f]">
        Save
    </button>
</div>
