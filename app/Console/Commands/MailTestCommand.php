<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class MailTestCommand extends Command
{
    protected $signature = 'mail:test {recipient : The email address to send the test message to}';

    protected $description = 'Send a test email through the configured mailer to verify SMTP/SES credentials and the global From address';

    public function handle(): int
    {
        $recipient = (string) $this->argument('recipient');

        if (! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            $this->error("\"{$recipient}\" is not a valid email address.");

            return self::FAILURE;
        }

        $mailer = (string) config('mail.default');
        $fromAddress = (string) config('mail.from.address');
        $fromName = (string) config('mail.from.name');

        $this->line("Mailer:       {$mailer}");
        $this->line("From:         {$fromName} <{$fromAddress}>");
        $this->line("Recipient:    {$recipient}");

        try {
            Mail::raw(
                "This is a test email from Surrey Tuning Services.\n\n".
                "If you received this message, your mail configuration is working.\n\n".
                'Mailer: '.$mailer."\n".
                'Sent at: '.now()->toDateTimeString().' UTC',
                function ($message) use ($recipient) {
                    $message->to($recipient)
                        ->subject('Surrey Tuning Services — Mail Configuration Test');
                }
            );
        } catch (Throwable $e) {
            $this->error('Failed to send test email: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info("Test email dispatched to {$recipient} via the \"{$mailer}\" mailer.");

        return self::SUCCESS;
    }
}
