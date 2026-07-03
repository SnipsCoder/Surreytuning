<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Tests\TestCase;

class TwoFactorEnforcementTest extends TestCase
{
    public function test_owner_without_two_factor_is_forced_to_setup(): void
    {
        $owner = User::factory()->create([
            'role' => UserRole::Owner,
            'two_factor_confirmed_at' => null,
        ]);

        $this->actingAs($owner)->get('/dashboard')
            ->assertRedirect(route('two-factor.setup'));
    }

    public function test_confirmed_user_without_session_verification_is_challenged(): void
    {
        $owner = User::factory()->create([
            'role' => UserRole::Owner,
            'two_factor_confirmed_at' => now(),
        ]);

        // No two_factor_verified session flag set for this request.
        $this->actingAs($owner)->get('/dashboard')
            ->assertRedirect(route('two-factor.challenge'));
    }

    public function test_verified_owner_reaches_the_portal(): void
    {
        $this->withSession(['two_factor_verified' => true]);

        $owner = User::factory()->create([
            'role' => UserRole::Owner,
            'two_factor_confirmed_at' => now(),
        ]);

        $this->actingAs($owner)->get('/dashboard')->assertOk();
    }

    public function test_two_factor_routes_are_never_intercepted(): void
    {
        // A confirmed-but-unverified owner must still be able to reach the
        // challenge route itself, otherwise enforcement would loop forever.
        $owner = User::factory()->create([
            'role' => UserRole::Owner,
            'two_factor_confirmed_at' => now(),
        ]);

        $this->actingAs($owner)->get(route('two-factor.challenge'))->assertOk();
    }
}
