<?php

namespace App\Listeners;

use App\Events\DealerApplicationApproved;
use App\Notifications\DealerApprovedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendDealerApprovalEmail implements ShouldQueue
{
    public function handle(DealerApplicationApproved $event): void
    {
        $event->user->notify(new DealerApprovedNotification($event->application));
    }
}
