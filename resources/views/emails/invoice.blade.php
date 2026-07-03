<x-emails.layout :subject="'Invoice ' . $reference">
    <h2>Invoice {{ $reference }}</h2>
    <p>Dear {{ $invoice->dealer->company_name }},</p>
    <p>Please find your invoice attached to this email as a PDF.</p>
    <p>
        <strong>Invoice:</strong> {{ $reference }}<br>
        <strong>Description:</strong> {{ $invoice->description }}<br>
        <strong>Amount Due:</strong> £{{ number_format($invoice->amount_gross, 2) }}<br>
        <strong>Issued:</strong> {{ $invoice->created_at->format('d/m/Y') }}
    </p>
    @if ($invoice->status === \App\Enums\InvoiceStatus::Paid)
        <p>This invoice has been paid in full. Thank you.</p>
    @else
        <p>You can review and settle this invoice through your dealer portal.</p>
        <a href="{{ $url }}" class="btn">View Invoice</a>
    @endif
</x-emails.layout>
