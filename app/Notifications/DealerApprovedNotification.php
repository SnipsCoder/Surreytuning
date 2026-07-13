<?php

namespace App\Notifications;

use App\Models\DealerApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Password;

class DealerApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public DealerApplication $application) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $token = Password::createToken($notifiable);
        $resetUrl = url(route('password.reset', [
            'token' => $token,
            'email' => $notifiable->email,
        ], false));

        return (new MailMessage)
            ->subject('Welcome to '.\App\Models\Setting::brandName().' — Set Your Password')
            ->view('emails.dealer-approved', [
                'application' => $this->application,
                'resetUrl' => $resetUrl,
            ]);
    }
}
