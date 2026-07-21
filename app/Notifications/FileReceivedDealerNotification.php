<?php

namespace App\Notifications;

use App\Models\FileRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FileReceivedDealerNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public FileRequest $fileRequest) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $ref = $this->fileRequest->request_number_formatted;
        $url = url('/my/file-requests/'.$this->fileRequest->id);

        return (new MailMessage)
            ->subject("File Request Received — {$ref}")
            ->view('emails.file-received-dealer', [
                'fileRequest' => $this->fileRequest,
                'ref' => $ref,
                'url' => $url,
            ]);
    }
}
