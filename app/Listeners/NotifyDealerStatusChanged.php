<?php

namespace App\Listeners;

use App\Enums\FileRequestStatus;
use App\Events\FileRequestStatusChanged;
use App\Models\User;
use App\Notifications\FileReadyNotification;
use App\Notifications\StatusChangedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyDealerStatusChanged implements ShouldQueue
{
    public function handle(FileRequestStatusChanged $event): void
    {
        $fileRequest = $event->fileRequest;

        $contact = User::where('dealer_id', $fileRequest->dealer_id)
            ->where('is_primary_contact', true)
            ->first();

        if ($contact && $contact->notify_file_requests_email) {
            $contact->notify(new StatusChangedNotification($fileRequest, $event->oldStatus));

            if ($fileRequest->status === FileRequestStatus::Responded) {
                $contact->notify(new FileReadyNotification($fileRequest));
            }
        }
    }
}
