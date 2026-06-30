<x-emails.layout :subject="'Payment Confirmed — ' . $invoice->invoice_number">
    <h2>Payment Confirmed</h2>
    <p>Your payment has been successfully processed. Thank you!</p>
    <p>
        <strong>Invoice:</strong> {{ $invoice->invoice_number }}<br>
        <strong>Description:</strong> {{ $invoice->description }}<br>
        <strong>Amount:</strong> £{{ number_format($invoice->amount_gross, 2) }}<br>
        <strong>Date:</strong> {{ $invoice->paid_at?->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i') }}
    </p>
    <a href="{{ $url }}" class="btn">View Invoice</a>
</x-emails.layout>
