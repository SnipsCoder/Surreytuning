<x-layouts.owner>
    <x-page-header :title="'Invoice #' . $invoice->invoice_number" :subtitle="$invoice->dealer->company_name">
        <a href="{{ route('owner.invoices.download', $invoice) }}"
           class="px-4 py-2 rounded-md bg-slate-700 text-white text-sm font-medium hover:bg-slate-600">
            Download PDF
        </a>

        @if ($invoice->status !== \App\Enums\InvoiceStatus::Void)
            <form method="POST" action="{{ route('owner.invoices.send', $invoice) }}"
                  onsubmit="return confirm('Email this invoice to the dealer?');">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-md bg-brand text-white text-sm font-medium hover:opacity-90">
                    Email to client
                </button>
            </form>
        @endif

        @if ($invoice->status === \App\Enums\InvoiceStatus::Issued)
            <form method="POST" action="{{ route('owner.invoices.mark-paid', $invoice) }}">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-md bg-green-600 text-white text-sm font-medium hover:bg-green-700">
                    Mark Paid
                </button>
            </form>

            <form method="POST" action="{{ route('owner.invoices.void', $invoice) }}" onsubmit="return confirm('Void this invoice?');">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-md bg-red-600 text-white text-sm font-medium hover:bg-red-700">
                    Void
                </button>
            </form>
        @endif
    </x-page-header>

    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow divide-y divide-gray-200 dark:divide-gray-700">
        <div class="px-6 py-4 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Invoice Details</h2>
            <x-status-badge :status="$invoice->status->label()" :colour="$invoice->status->colour()" />
        </div>

        <dl class="px-6 py-4 grid grid-cols-2 gap-x-6 gap-y-4 text-sm">
            <div>
                <dt class="text-gray-500 dark:text-gray-400">Invoice Number</dt>
                <dd class="font-mono text-gray-900 dark:text-gray-100">#{{ $invoice->invoice_number }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400">Type</dt>
                <dd class="text-gray-900 dark:text-gray-100">{{ $invoice->type->label() }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400">Dealer</dt>
                <dd class="text-gray-900 dark:text-gray-100">{{ $invoice->dealer->company_name }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400">Raised By</dt>
                <dd class="text-gray-900 dark:text-gray-100">{{ $invoice->user?->name ?? '—' }}</dd>
            </div>
            <div class="col-span-2">
                <dt class="text-gray-500 dark:text-gray-400">Description</dt>
                <dd class="text-gray-900 dark:text-gray-100">{{ $invoice->description }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400">Issued</dt>
                <dd class="text-gray-900 dark:text-gray-100">{{ $invoice->created_at->format('d M Y') }}</dd>
            </div>
            @if ($invoice->paid_at)
            <div>
                <dt class="text-gray-500 dark:text-gray-400">Paid</dt>
                <dd class="text-gray-900 dark:text-gray-100">{{ $invoice->paid_at->format('d M Y') }}</dd>
            </div>
            @endif
            @if ($invoice->stripe_payment_intent_id)
            <div class="col-span-2">
                <dt class="text-gray-500 dark:text-gray-400">Stripe Payment Intent</dt>
                <dd class="font-mono text-xs text-gray-700 dark:text-gray-300">{{ $invoice->stripe_payment_intent_id }}</dd>
            </div>
            @endif
        </dl>

        <div class="px-6 py-4">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 dark:text-gray-400">
                        <th class="pb-2 font-medium">Description</th>
                        <th class="pb-2 font-medium text-right">Net</th>
                        <th class="pb-2 font-medium text-right">VAT</th>
                        <th class="pb-2 font-medium text-right">Gross</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <tr>
                        <td class="py-2 text-gray-900 dark:text-gray-100">{{ $invoice->description }}</td>
                        <td class="py-2 text-right text-gray-700 dark:text-gray-300">£{{ number_format($invoice->amount_net, 2) }}</td>
                        <td class="py-2 text-right text-gray-700 dark:text-gray-300">£{{ number_format($invoice->vat_amount, 2) }}</td>
                        <td class="py-2 text-right font-semibold text-gray-900 dark:text-gray-100">£{{ number_format($invoice->amount_gross, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('invoices.index') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:underline">&larr; Back to invoices</a>
    </div>
</x-layouts.owner>
