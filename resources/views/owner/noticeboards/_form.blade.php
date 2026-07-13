<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title</label>
    <input type="text" name="title" value="{{ old('title', $noticeboard?->title) }}" required
        class="mt-1 block w-full rounded-md border-gray-300 dark:border-[#2a2a2a] dark:bg-[#0d0d0d] dark:text-gray-100 text-sm shadow-sm">
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Body</label>
    <textarea name="body" rows="4"
        class="mt-1 block w-full rounded-md border-gray-300 dark:border-[#2a2a2a] dark:bg-[#0d0d0d] dark:text-gray-100 text-sm shadow-sm">{{ old('body', $noticeboard?->body) }}</textarea>
</div>

<div class="grid grid-cols-3 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Priority</label>
        <select name="priority" required
            class="mt-1 block w-full rounded-md border-gray-300 dark:border-[#2a2a2a] dark:bg-[#0d0d0d] dark:text-gray-100 text-sm shadow-sm">
            @foreach (\App\Enums\NoticePriority::cases() as $priority)
                <option value="{{ $priority->value }}" @selected(old('priority', $noticeboard?->priority?->value) === $priority->value)>
                    {{ $priority->label() }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Show From</label>
        <input type="date" name="show_from" value="{{ old('show_from', $noticeboard?->show_from?->format('Y-m-d')) }}"
            class="mt-1 block w-full rounded-md border-gray-300 dark:border-[#2a2a2a] dark:bg-[#0d0d0d] dark:text-gray-100 text-sm shadow-sm">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Show Until</label>
        <input type="date" name="show_until" value="{{ old('show_until', $noticeboard?->show_until?->format('Y-m-d')) }}"
            class="mt-1 block w-full rounded-md border-gray-300 dark:border-[#2a2a2a] dark:bg-[#0d0d0d] dark:text-gray-100 text-sm shadow-sm">
    </div>
</div>

<div class="flex items-center gap-6">
    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $noticeboard?->is_active ?? true))>
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
