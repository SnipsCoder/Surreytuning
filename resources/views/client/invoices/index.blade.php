<x-layouts.client>
    <x-page-header title="Invoices" subtitle="View and pay your invoices" />

    @if ($invoices->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-sm text-gray-500 dark:text-gray-400">
            You have no invoices yet.
        </div>
    @else
        <x-data-table :headers="['Invoice #', 'Description', 'Amount', 'Status', 'Date', '']">
            @foreach ($invoices as $invoice)
                <tr>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                        {{ $invoice->invoice_number }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                        {{ $invoice->description }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                        £{{ number_format($invoice->amount_gross, 2) }}
                    </td>
                    <td class="px-6 py-4">
                        <x-status-badge :status="$invoice->status->label()" :colour="$invoice->status->colour()" />
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                        {{ $invoice->created_at->format('d/m/Y') }}
                    </td>
                    <td class="px-6 py-4 text-right text-sm">
                        <a href="{{ route('client.invoices.show', $invoice) }}" class="text-orange-600 hover:text-orange-800">View</a>
                    </td>
                </tr>
            @endforeach
        </x-data-table>

        <div class="mt-4">{{ $invoices->links() }}</div>
    @endif
</x-layouts.client>
