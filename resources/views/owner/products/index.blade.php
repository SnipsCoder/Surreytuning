<x-layouts.owner>
    <x-page-header title="Products" subtitle="Shop products available to dealers">
        <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-product' }))"
            class="px-4 py-2 rounded-md bg-brand text-white text-sm font-medium hover:bg-[#c92a0f]">
            Add Product
        </button>
    </x-page-header>

    <x-data-table :headers="['Image', 'Name', 'Price (Net)', 'Payment Type', 'Stock', 'Active', '']">
        @forelse ($products as $product)
            <tr class="hover:bg-gray-50 dark:hover:bg-[#1a1a1a]/60">
                <td class="px-4 py-3 text-sm">
                    @if ($product->image_path)
                        <img src="{{ route('products.image', $product) }}" alt="{{ $product->name }}" class="h-10 w-10 object-cover rounded-md">
                    @else
                        <div class="h-10 w-10 rounded-md bg-gray-200 dark:bg-gray-700"></div>
                    @endif
                </td>
                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $product->name }}</td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">£{{ number_format((float) $product->price_net, 2) }}</td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $product->payment_type->label() }}</td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $product->stock ?? 'Unlimited' }}</td>
                <td class="px-4 py-3 text-sm">
                    @if ($product->is_active)
                        <x-status-badge status="Active" colour="bg-green-100 text-green-800" />
                    @else
                        <x-status-badge status="Inactive" colour="bg-gray-100 text-gray-800" />
                    @endif
                </td>
                <td class="px-4 py-3 text-sm text-right space-x-3">
                    <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-product-{{ $product->id }}' }))"
                        class="text-indigo-600 dark:text-indigo-400 hover:underline">Edit</button>
                    <form method="POST" action="{{ route('products.destroy', $product) }}" class="inline" onsubmit="return confirm('Delete this product?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">Delete</button>
                    </form>
                </td>
            </tr>

            <x-modal id="edit-product-{{ $product->id }}" title="Edit Product">
                <form method="POST" action="{{ route('products.update', $product) }}" class="space-y-4" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    @include('owner.products._form', ['product' => $product])
                </form>
            </x-modal>
        @empty
            <tr>
                <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No products found.</td>
            </tr>
        @endforelse
    </x-data-table>

    <x-modal id="create-product" title="Add Product">
        <form method="POST" action="{{ route('products.store') }}" class="space-y-4" enctype="multipart/form-data">
            @csrf
            @include('owner.products._form', ['product' => null])
        </form>
    </x-modal>
</x-layouts.owner>
