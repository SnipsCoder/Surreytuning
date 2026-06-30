<x-layouts.owner>
    <x-page-header title="Invoices" subtitle="All invoices across all dealers">
        <button
            x-data
            @click="$dispatch('open-modal', 'create-invoice')"
            class="px-4 py-2 rounded-md bg-gray-900 dark:bg-gray-700 text-white text-sm font-medium hover:bg-gray-800 dark:hover:bg-gray-600">
            New Invoice
        </button>
    </x-page-header>

    <form method="GET" action="{{ route('invoices.index') }}" class="mb-6 flex flex-wrap items-center gap-3">
        <select name="status" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm shadow-sm">
            <option value="">All statuses</option>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>

        <select name="dealer_id" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm shadow-sm">
            <option value="">All dealers</option>
            @foreach ($dealers as $dealer)
                <option value="{{ $dealer->id }}" @selected(request('dealer_id') == $dealer->id)>{{ $dealer->company_name }}</option>
            @endforeach
        </select>

        <button type="submit" class="px-4 py-2 rounded-md bg-gray-900 dark:bg-gray-700 text-white text-sm font-medium hover:bg-gray-800 dark:hover:bg-gray-600">
            Filter
        </button>

        @if (request('status') || request('dealer_id'))
            <a href="{{ route('invoices.index') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:underline">Clear</a>
        @endif
    </form>

    <x-data-table :headers="['Invoice #', 'Dealer', 'Description', 'Net', 'VAT', 'Gross', 'Status', 'Date', '']">
        @forelse ($invoices as $invoice)
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/60">
                <td class="px-4 py-3 text-sm font-mono text-gray-900 dark:text-gray-100">#{{ $invoice->invoice_number }}</td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $invoice->dealer->company_name }}</td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300 max-w-xs truncate">{{ $invoice->description }}</td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">£{{ number_format($invoice->amount_net, 2) }}</td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">£{{ number_format($invoice->vat_amount, 2) }}</td>
                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">£{{ number_format($invoice->amount_gross, 2) }}</td>
                <td class="px-4 py-3 text-sm">
                    <x-status-badge :status="$invoice->status->label()" :colour="$invoice->status->colour()" />
                </td>
                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $invoice->created_at->format('d M Y') }}</td>
                <td class="px-4 py-3 text-sm text-right space-x-2 whitespace-nowrap">
                    <a href="{{ route('invoices.show', $invoice) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">View</a>

                    @if ($invoice->status === \App\Enums\InvoiceStatus::Issued)
                        <form method="POST" action="{{ route('owner.invoices.mark-paid', $invoice) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-green-600 dark:text-green-400 hover:underline">Mark Paid</button>
                        </form>

                        <form method="POST" action="{{ route('owner.invoices.void', $invoice) }}" class="inline" onsubmit="return confirm('Void this invoice?');">
                            @csrf
                            <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">Void</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No invoices found.</td>
            </tr>
        @endforelse
    </x-data-table>

    <div class="mt-4">
        {{ $invoices->links() }}
    </div>

    <x-modal id="create-invoice" title="New Manual Invoice">
        <form method="POST" action="{{ route('invoices.store') }}" class="space-y-4">
            @csrf

            <div>
                <label for="dealer_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dealer</label>
                <select id="dealer_id" name="dealer_id" required
                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Select dealer...</option>
                    @foreach ($dealers as $dealer)
                        <option value="{{ $dealer->id }}" @selected(old('dealer_id') == $dealer->id)>{{ $dealer->company_name }}</option>
                    @endforeach
                </select>
                @error('dealer_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                <input type="text" id="description" name="description" value="{{ old('description') }}" required maxlength="500"
                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="amount_net" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Net Amount (£)</label>
                <input type="number" id="amount_net" name="amount_net" value="{{ old('amount_net') }}" required min="0.01" step="0.01"
                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @error('amount_net') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" id="apply_vat" name="apply_vat" value="1" @checked(old('apply_vat'))
                    class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 focus:ring-indigo-500">
                <label for="apply_vat" class="text-sm text-gray-700 dark:text-gray-300">Apply VAT</label>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" x-on:click="open = false" class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:underline">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-md bg-gray-900 dark:bg-gray-700 text-white text-sm font-medium hover:bg-gray-800 dark:hover:bg-gray-600">
                    Create Invoice
                </button>
            </div>
        </form>
    </x-modal>
</x-layouts.owner>

