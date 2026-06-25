<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">File Stage</label>
    <select name="file_stage_id" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm">
        @foreach ($fileStages as $stage)
            <option value="{{ $stage->id }}" @selected(old('file_stage_id', $fileOption?->file_stage_id) === $stage->id)>{{ $stage->name }}</option>
        @endforeach
    </select>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
    <input type="text" name="name" value="{{ old('name', $fileOption?->name) }}" required
        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm">
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
    <textarea name="description" rows="2"
        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm">{{ old('description', $fileOption?->description) }}</textarea>
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Price (Net)</label>
        <input type="number" name="price_net" step="0.01" min="0" value="{{ old('price_net', $fileOption?->price_net) }}" required
            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sort Order</label>
        <input type="number" name="sort_order" value="{{ old('sort_order', $fileOption?->sort_order ?? 0) }}"
            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm">
    </div>
</div>

<div class="flex items-center gap-6">
    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
        <input type="checkbox" name="vat_applicable" value="1" @checked(old('vat_applicable', $fileOption?->vat_applicable ?? true))>
        VAT Applicable
    </label>

    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
        <input type="checkbox" name="is_required" value="1" @checked(old('is_required', $fileOption?->is_required ?? false))>
        Required
    </label>

    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $fileOption?->is_active ?? true))>
        Active
    </label>
</div>

<div class="flex justify-end gap-3 pt-2">
    <button type="button" x-on:click="open = false" class="px-4 py-2 rounded-md text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
        Cancel
    </button>
    <button type="submit" class="px-4 py-2 rounded-md bg-[#e63012] text-white text-sm font-medium hover:bg-[#c92a0f]">
        Save
    </button>
</div>
