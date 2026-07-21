<?php

namespace App\Notifications;

use App\Models\FileRequestMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public FileRequestMessage $message) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $fileRequest = $this->message->fileRequest;
        $ref = $fileRequest->request_number_formatted;
        $url = url((in_array($notifiable->role?->value, ['dealer_owner', 'dealer_user'], true) ? '/my/file-requests/' : '/file-requests/').$fileRequest->id);

        return (new MailMessage)
            ->subject("New Message on File Request {$ref}")
            ->view('emails.new-message', [
                'threadMessage' => $this->message,
                'fileRequest' => $fileRequest,
                'ref' => $ref,
                'url' => $url,
            ]);
    }
}
