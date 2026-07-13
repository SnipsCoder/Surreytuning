<x-layouts.client>
    <x-page-header title="Invoices" subtitle="View and pay your invoices" />

    <div class="flex gap-2 mb-5">
        <a href="{{ route('client.invoices.index') }}"
           class="px-3 py-1.5 rounded text-sm font-medium {{ ! $currentStatus ? 'bg-brand text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700' }}">
            All
        </a>
        @foreach (['issued' => 'Issued', 'paid' => 'Paid', 'void' => 'Void'] as $value => $label)
            <a href="{{ route('client.invoices.index', ['status' => $value]) }}"
               class="px-3 py-1.5 rounded text-sm font-medium {{ $currentStatus === $value ? 'bg-brand text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    @if ($invoices->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-sm text-gray-500 dark:text-gray-400">
            No invoices found.
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
                    <td class="px-6 py-4 text-right text-sm whitespace-nowrap space-x-4">
                        <a href="{{ route('client.invoices.pdf', $invoice) }}" target="_blank"
                           class="text-brand hover:text-[#c42910] font-medium">View PDF</a>
                        <a href="{{ route('client.invoices.show', $invoice) }}"
                           class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">Details</a>
                    </td>
                </tr>
            @endforeach
        </x-data-table>

        <div class="mt-4">{{ $invoices->links() }}</div>
    @endif
</x-layouts.client>
