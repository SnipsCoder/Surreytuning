<x-layouts.client>
    <x-page-header title="Products" subtitle="Browse and purchase products" />

    @if ($products->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-sm text-gray-500 dark:text-gray-400">
            No products are currently available.
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($products as $product)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5 flex flex-col">
                    @if ($product->image_path)
                        <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}" class="rounded mb-3 h-32 object-cover">
                    @endif
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $product->name }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">{{ $product->description }}</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-1">
                        £{{ number_format($product->price_net, 2) }}{{ $product->vat_applicable ? ' + VAT' : '' }}
                    </p>
                    @php $inStock = is_null($product->stock) || $product->stock > 0; @endphp
                    <p class="text-xs text-gray-400 mb-4">
                        Stock: {{ is_null($product->stock) ? 'Unlimited' : $product->stock }}
                    </p>

                    <form method="POST" action="{{ route('client.products.purchase', $product) }}" class="mt-auto space-y-2">
                        @csrf

                        @if ($product->payment_type->value === 'both')
                            <select name="payment_method" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                                <option value="stripe">Pay by Card</option>
                                <option value="file_credits">Pay with File Credits</option>
                            </select>
                        @else
                            <input type="hidden" name="payment_method" value="{{ $product->payment_type->value === 'file_credits' ? 'file_credits' : 'stripe' }}">
                        @endif

                        <x-primary-button type="submit" class="w-full justify-center" :disabled="! $inStock">
                            {{ $inStock ? 'Purchase' : 'Out of Stock' }}
                        </x-primary-button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.client>
