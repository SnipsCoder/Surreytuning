<?php

namespace App\Listeners;

use App\Events\DealerApplicationRejected;
use App\Notifications\DealerRejectedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendDealerRejectionEmail implements ShouldQueue
{
    public function handle(DealerApplicationRejected $event): void
    {
        Notification::route('mail', $event->application->email)
            ->notify(new DealerRejectedNotification($event->application));
    }
}
