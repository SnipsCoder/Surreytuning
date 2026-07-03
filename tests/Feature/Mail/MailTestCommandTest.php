<?php

namespace Tests\Feature\Mail;

use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MailTestCommandTest extends TestCase
{
    public function test_it_sends_a_test_email_to_a_valid_recipient(): void
    {
        Mail::fake();

        $this->artisan('mail:test', ['recipient' => 'ops@surreytuning.test'])
            ->assertSuccessful();
    }

    public function test_it_rejects_an_invalid_recipient(): void
    {
        Mail::fake();

        $this->artisan('mail:test', ['recipient' => 'not-an-email'])
            ->assertFailed();

        Mail::assertNothingSent();
    }
}
