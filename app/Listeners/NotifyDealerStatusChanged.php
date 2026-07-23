<?php

namespace App\Listeners;

use App\Enums\FileRequestStatus;
use App\Events\FileRequestStatusChanged;
use App\Models\User;
use App\Notifications\FileReadyNotification;
use App\Notifications\StatusChangedNotification;
use App\Services\WhatsAppService;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyDealerStatusChanged implements ShouldQueue
{
    public function __construct(private WhatsAppService $whatsApp) {}

    public function handle(FileRequestStatusChanged $event): void
    {
        $fileRequest = $event->fileRequest;

        $contact = User::where('dealer_id', $fileRequest->dealer_id)
            ->where('is_primary_contact', true)
            ->first();

        if (! $contact) {
            return;
        }

        if ($contact->notify_file_requests_email) {
            $contact->notify(new StatusChangedNotification($fileRequest, $event->oldStatus));

            if ($fileRequest->status === FileRequestStatus::Responded) {
                $contact->notify(new FileReadyNotification($fileRequest));
            }
        }

        // WhatsApp "job complete" — sent when the file is ready and the contact
        // has linked a WhatsApp number (presence of the number is the opt-in).
        // Never fails the listener; logs and skips until the API is configured.
        if ($fileRequest->status === FileRequestStatus::Responded && filled($contact->whatsapp_number)) {
            $this->whatsApp->sendJobComplete($contact, $fileRequest);
        }
    }
}
