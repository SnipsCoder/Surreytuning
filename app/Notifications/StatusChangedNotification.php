<?php

namespace App\Notifications;

use App\Enums\FileRequestStatus;
use App\Models\FileRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public FileRequest $fileRequest,
        public FileRequestStatus $oldStatus,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $ref = $this->fileRequest->request_number_formatted;
        $url = url('/file-requests/'.$this->fileRequest->id);

        return (new MailMessage)
            ->subject("File Request Status Update — {$ref}")
            ->view('emails.status-changed', [
                'fileRequest' => $this->fileRequest,
                'oldStatus' => $this->oldStatus,
                'newStatus' => $this->fileRequest->status,
                'ref' => $ref,
                'url' => $url,
            ]);
    }
}
