<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Invoice;
use App\Models\Setting;
use App\Services\InvoicePdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $reference;

    public string $url;

    public function __construct(public Invoice $invoice)
    {
        $prefix = Setting::get()->invoice_reference_prefix ?: 'INV';
        $this->reference = $prefix.'-'.$invoice->invoice_number;
        $this->url = route('client.invoices.show', $invoice);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Invoice '.$this->reference);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.invoice');
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $pdf = app(InvoicePdfService::class);

        return [
            Attachment::fromData(
                fn () => $pdf->make($this->invoice)->output(),
                $pdf->filename($this->invoice),
            )->withMime('application/pdf'),
        ];
    }
}
