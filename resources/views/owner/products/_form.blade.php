<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
    <input type="text" name="name" value="{{ old('name', $product?->name) }}" required
        class="mt-1 block w-full rounded-md border-gray-300 dark:border-[#2a2a2a] dark:bg-[#0d0d0d] dark:text-gray-100 text-sm shadow-sm">
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
    <textarea name="description" rows="3"
        class="mt-1 block w-full rounded-md border-gray-300 dark:border-[#2a2a2a] dark:bg-[#0d0d0d] dark:text-gray-100 text-sm shadow-sm">{{ old('description', $product?->description) }}</textarea>
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Price (Net)</label>
        <input type="number" name="price_net" step="0.01" min="0" value="{{ old('price_net', $product?->price_net) }}" required
            class="mt-1 block w-full rounded-md border-gray-300 dark:border-[#2a2a2a] dark:bg-[#0d0d0d] dark:text-gray-100 text-sm shadow-sm">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Stock (blank = unlimited)</label>
        <input type="number" name="stock" min="0" value="{{ old('stock', $product?->stock) }}"
            class="mt-1 block w-full rounded-md border-gray-300 dark:border-[#2a2a2a] dark:bg-[#0d0d0d] dark:text-gray-100 text-sm shadow-sm">
    </div>
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Type</label>
        <select name="payment_type" required
            class="mt-1 block w-full rounded-md border-gray-300 dark:border-[#2a2a2a] dark:bg-[#0d0d0d] dark:text-gray-100 text-sm shadow-sm">
            @foreach (\App\Enums\ProductPaymentType::cases() as $paymentType)
                <option value="{{ $paymentType->value }}" @selected(old('payment_type', $product?->payment_type?->value) === $paymentType->value)>
                    {{ $paymentType->label() }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sort Order</label>
        <input type="number" name="sort_order" value="{{ old('sort_order', $product?->sort_order) }}"
            class="mt-1 block w-full rounded-md border-gray-300 dark:border-[#2a2a2a] dark:bg-[#0d0d0d] dark:text-gray-100 text-sm shadow-sm">
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Image</label>
    @if ($product?->image_path)
        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->image_path) }}" alt="{{ $product->name }}" class="mt-2 h-16 w-16 object-cover rounded-md">
    @endif
    <input type="file" name="image" accept="image/*"
        class="mt-2 block w-full text-sm text-gray-700 dark:text-gray-300">
</div>

<div class="flex items-center gap-6">
    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
        <input type="checkbox" name="vat_applicable" value="1" @checked(old('vat_applicable', $product?->vat_applicable ?? true))>
        VAT Applicable
    </label>

    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product?->is_active ?? true))>
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
