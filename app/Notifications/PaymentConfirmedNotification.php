<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Invoice $invoice)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url('/invoices/' . $this->invoice->id);

        return (new MailMessage)
            ->subject("Payment Confirmed — {$this->invoice->invoice_number}")
            ->view('emails.payment-confirmed', [
                'invoice' => $this->invoice,
                'url' => $url,
            ]);
    }
}
