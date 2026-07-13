<?php

namespace App\Notifications;

use App\Models\DealerApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DealerRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public DealerApplication $application) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(\App\Models\Setting::brandName().' — Application Update')
            ->view('emails.dealer-rejected', [
                'application' => $this->application,
            ]);
    }
}
