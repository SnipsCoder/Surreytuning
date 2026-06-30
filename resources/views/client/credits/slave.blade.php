<x-layouts.client>
    <x-page-header title="Slave Credits" subtitle="Top up and track your slave credit balance" />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <x-stat-card label="Current Balance" value="£{{ number_format($dealer->slave_credit_balance, 2) }}" colour="blue" />
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">Top Up Credits</h3>

        <form method="POST" action="{{ route('client.credits.slave.checkout') }}" class="flex flex-wrap items-end gap-4">
            @csrf
            <div>
                <x-input-label for="amount" value="Amount (£)" />
                <x-text-input id="amount" name="amount" type="number" step="0.01" min="10" max="5000" value="50" class="mt-1 w-40" required />
                <x-input-error :messages="$errors->get('amount')" class="mt-1" />
            </div>
            <x-primary-button type="submit">Top Up via Stripe</x-primary-button>
        </form>
    </div>

    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Transaction History</h2>

    @if ($transactions->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-sm text-gray-500 dark:text-gray-400">
            No slave credit transactions yet.
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
                        £{{ number_format($transaction->amount, 2) }}
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
