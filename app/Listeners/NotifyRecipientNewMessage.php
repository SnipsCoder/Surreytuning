<?php

namespace App\Listeners;

use App\Enums\UserRole;
use App\Events\NewMessagePosted;
use App\Models\User;
use App\Notifications\NewMessageNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyRecipientNewMessage implements ShouldQueue
{
    public function handle(NewMessagePosted $event): void
    {
        $message = $event->message;
        $fileRequest = $message->fileRequest;
        $sender = $message->sender;

        if (! $sender) {
            return;
        }

        $isOwnerSender = in_array($sender->role, [UserRole::Owner, UserRole::Tuner]);

        if ($isOwnerSender) {
            // Notify dealer primary contact
            $recipient = User::where('dealer_id', $fileRequest->dealer_id)
                ->where('is_primary_contact', true)
                ->first();

            if ($recipient && $recipient->notify_comments_email) {
                $recipient->notify(new NewMessageNotification($message));
            }
        } else {
            // Notify owner team
            User::ownerTeam()
                ->where('notify_comments_email', true)
                ->each(fn (User $user) => $user->notify(new NewMessageNotification($message)));
        }
    }
}
