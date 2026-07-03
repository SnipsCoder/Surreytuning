<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title</label>
    <input type="text" name="title" value="{{ old('title', $whatsNew?->title) }}" required
        class="mt-1 block w-full rounded-md border-gray-300 dark:border-[#2a2a2a] dark:bg-[#0d0d0d] dark:text-gray-100 text-sm shadow-sm">
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Body</label>
    <textarea name="body" rows="4" required
        class="mt-1 block w-full rounded-md border-gray-300 dark:border-[#2a2a2a] dark:bg-[#0d0d0d] dark:text-gray-100 text-sm shadow-sm">{{ old('body', $whatsNew?->body) }}</textarea>
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Version</label>
        <input type="text" name="version" value="{{ old('version', $whatsNew?->version) }}"
            class="mt-1 block w-full rounded-md border-gray-300 dark:border-[#2a2a2a] dark:bg-[#0d0d0d] dark:text-gray-100 text-sm shadow-sm">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Published At</label>
        <input type="date" name="published_at" value="{{ old('published_at', $whatsNew?->published_at?->format('Y-m-d')) }}"
            class="mt-1 block w-full rounded-md border-gray-300 dark:border-[#2a2a2a] dark:bg-[#0d0d0d] dark:text-gray-100 text-sm shadow-sm">
    </div>
</div>

<div class="flex justify-end gap-3 pt-2">
    <button type="button" x-on:click="open = false" class="px-4 py-2 rounded-md text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
        Cancel
    </button>
    <button type="submit" class="px-4 py-2 rounded-md bg-[#e63012] text-white text-sm font-medium hover:bg-[#c92a0f]">
        Save
    </button>
</div>
