<?php

namespace App\Listeners;

use App\Events\PaymentConfirmed;
use App\Models\User;
use App\Notifications\PaymentConfirmedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPaymentConfirmationEmail implements ShouldQueue
{
    public function handle(PaymentConfirmed $event): void
    {
        $contact = User::where('dealer_id', $event->dealer->id)
            ->where('is_primary_contact', true)
            ->first();

        if ($contact) {
            $contact->notify(new PaymentConfirmedNotification($event->invoice));
        }
    }
}
