<x-layouts.client>
    <x-page-header title="Invoice #{{ $invoice->invoice_number }}" subtitle="{{ $invoice->description }}">
        <a href="{{ route('client.invoices.pdf', $invoice) }}" target="_blank"
           class="px-4 py-2 rounded-md bg-brand text-white text-sm font-medium hover:opacity-90">
            View PDF
        </a>

        <a href="{{ route('client.invoices.download', $invoice) }}"
           class="px-4 py-2 rounded-md bg-slate-700 text-white text-sm font-medium hover:bg-slate-600">
            Download PDF
        </a>

        @if ($invoice->status === \App\Enums\InvoiceStatus::Issued)
            <form method="POST" action="{{ route('client.invoices.pay', $invoice) }}">
                @csrf
                <x-primary-button type="submit">Pay Now</x-primary-button>
            </form>
        @endif
    </x-page-header>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 max-w-lg">
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between">
                <dt class="text-gray-500 dark:text-gray-400">Status</dt>
                <dd><x-status-badge :status="$invoice->status->label()" :colour="$invoice->status->colour()" /></dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-500 dark:text-gray-400">Net Amount</dt>
                <dd class="text-gray-900 dark:text-gray-100">£{{ number_format($invoice->amount_net, 2) }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-500 dark:text-gray-400">VAT</dt>
                <dd class="text-gray-900 dark:text-gray-100">£{{ number_format($invoice->vat_amount, 2) }}</dd>
            </div>
            <div class="flex justify-between font-semibold">
                <dt class="text-gray-900 dark:text-gray-100">Total</dt>
                <dd class="text-gray-900 dark:text-gray-100">£{{ number_format($invoice->amount_gross, 2) }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-500 dark:text-gray-400">Issued</dt>
                <dd class="text-gray-900 dark:text-gray-100">{{ $invoice->created_at->format('d/m/Y') }}</dd>
            </div>
            @if ($invoice->paid_at)
                <div class="flex justify-between">
                    <dt class="text-gray-500 dark:text-gray-400">Paid</dt>
                    <dd class="text-gray-900 dark:text-gray-100">{{ $invoice->paid_at->format('d/m/Y H:i') }}</dd>
                </div>
            @endif
        </dl>
    </div>
</x-layouts.client>
