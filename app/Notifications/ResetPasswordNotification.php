<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;

/**
 * Branded password-reset email. Replaces Laravel's stock ResetPassword
 * notification so the message uses the portal's own email layout
 * (logo, brand colour, footer) like every other transactional email.
 */
class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $expireMinutes = Config::get('auth.passwords.'.Config::get('auth.defaults.passwords').'.expire', 60);

        $subject = 'Reset Your Password — '.\App\Models\Setting::brandName();

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.reset-password', [
                'subject' => $subject,
                'name' => trim((string) ($notifiable->first_name ?? '')) ?: 'there',
                'url' => $url,
                'expireMinutes' => $expireMinutes,
            ]);
    }
}
