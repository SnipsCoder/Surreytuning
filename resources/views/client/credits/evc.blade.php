<x-layouts.client>
    <x-page-header title="EVC Credits" subtitle="Purchase EVC credit bundles and track your balance" />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <x-stat-card label="Current Balance" value="{{ number_format($dealer->evc_credit_balance, 2) }}" colour="blue" />
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">Available Bundles</h3>

        @if ($bundles->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">No EVC bundles are currently available.</p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach ($bundles as $bundle)
                    <form method="POST" action="{{ route('client.credits.evc.checkout') }}" class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 flex flex-col">
                        @csrf
                        <input type="hidden" name="winols_bundle_id" value="{{ $bundle->id }}">
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $bundle->name }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">{{ $bundle->credits }} credits</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">£{{ number_format($bundle->price_net, 2) }}</p>
                        <x-primary-button type="submit" class="mt-auto">Purchase</x-primary-button>
                    </form>
                @endforeach
            </div>
        @endif
    </div>

    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Transaction History</h2>

    @if ($transactions->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-sm text-gray-500 dark:text-gray-400">
            No EVC credit transactions yet.
        </div>
    @else
        <x-data-table :headers="['Date', 'Type', 'Amount', 'Reason']">
            @foreach ($transactions as $transaction)
                <tr>
                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                        {{ $transaction->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                        {{ $transaction->type->label() }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                        {{ number_format($transaction->amount, 2) }}
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
