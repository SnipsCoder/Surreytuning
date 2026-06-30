<x-layouts.client>
    <x-page-header title="Slave Credits" subtitle="Top up and track your slave credit balance" />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <x-stat-card label="Current Balance" value="£{{ number_format($dealer->slave_credit_balance, 2) }}" colour="blue" />
    </div>

    @if ($products->isNotEmpty())
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Top Up Credits</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            @foreach ($products as $product)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5 flex flex-col justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-1">{{ $product->name }}</h3>
                        @if ($product->description)
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">{{ $product->description }}</p>
                        @endif
                        <p class="text-2xl font-bold text-orange-600 mb-1">£{{ number_format($product->price_net, 2) }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500">= {{ number_format($product->price_net, 2) }} slave credits (no VAT)</p>
                    </div>
                    <form method="POST" action="{{ route('client.credits.slave.checkout') }}" class="mt-4">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <x-primary-button type="submit" class="w-full justify-center">Buy via Stripe</x-primary-button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg p-4 mb-6 text-sm text-red-700 dark:text-red-300">
            {{ $errors->first() }}
        </div>
    @endif

    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Transaction History</h2>

    @if ($transactions->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-sm text-gray-500 dark:text-gray-400">
            No slave credit transactions yet.
        </div>
    @else
        <x-data-table :headers="['Date', 'Type', 'Amount', 'Balance After', 'Reason']">
            @foreach ($transactions as $transaction)
                <tr>
                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                        {{ $transaction->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                        {{ $transaction->type->label() }}
                    </td>
                    <td class="px-6 py-4 text-sm font-medium {{ $transaction->amount >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ $transaction->amount >= 0 ? '+' : '' }}£{{ number_format($transaction->amount, 2) }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                        £{{ number_format($transaction->balance_after, 2) }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                        {{ $transaction->reason }}
                    </td>
                </tr>
            @endforeach
        </x-data-table>

        <div class="mt-4">{{ $transactions->links() }}</div>
    @endif
</x-layouts.client>
