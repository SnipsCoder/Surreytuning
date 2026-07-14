<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

class RegistrationTest extends TestCase
{
    public function test_registration_screen_is_disabled(): void
    {
        // Public self-registration is intentionally removed — this is an
        // invite/application-only portal (dealers join via /apply).
        $this->get('/register')->assertNotFound();
    }

    public function test_new_users_cannot_self_register(): void
    {
        $this->post('/register', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertNotFound();

        $this->assertGuest();
    }
}
