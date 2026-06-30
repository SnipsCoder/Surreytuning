<?php

namespace App\Listeners;

use App\Events\FileRequestSubmitted;
use App\Models\User;
use App\Notifications\NewFileRequestOwnerNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyOwnerNewFileRequest implements ShouldQueue
{
    public function handle(FileRequestSubmitted $event): void
    {
        $fileRequest = $event->fileRequest;

        User::ownerTeam()
            ->where('notify_file_requests_email', true)
            ->each(fn (User $user) => $user->notify(new NewFileRequestOwnerNotification($fileRequest)));
    }
}
