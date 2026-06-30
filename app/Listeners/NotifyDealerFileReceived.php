<?php

namespace App\Listeners;

use App\Events\FileRequestSubmitted;
use App\Notifications\FileReceivedDealerNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyDealerFileReceived implements ShouldQueue
{
    public function handle(FileRequestSubmitted $event): void
    {
        $fileRequest = $event->fileRequest;
        $user = $fileRequest->submittedBy;

        if ($user && $user->notify_file_requests_email) {
            $user->notify(new FileReceivedDealerNotification($fileRequest));
        }
    }
}
