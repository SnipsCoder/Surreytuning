<?php

namespace App\Notifications;

use App\Models\FileRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FileReadyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public FileRequest $fileRequest,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $ref = $this->fileRequest->request_number_formatted;
        $url = url('/my/file-requests/'.$this->fileRequest->id);

        $vehicle = trim(collect([
            $this->fileRequest->make,
            $this->fileRequest->model,
            $this->fileRequest->year,
        ])->filter()->implode(' '));

        return (new MailMessage)
            ->subject("Your tuning file is ready — {$ref}")
            ->view('emails.file-ready', [
                'fileRequest' => $this->fileRequest,
                'ref' => $ref,
                'url' => $url,
                'vehicle' => $vehicle,
                'stage' => $this->fileRequest->fileStage?->name,
                'contactName' => $notifiable->first_name ?? 'there',
            ]);
    }
}
