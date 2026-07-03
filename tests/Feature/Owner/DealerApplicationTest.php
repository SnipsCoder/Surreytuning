<?php

namespace Tests\Feature\Owner;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Models\DealerApplication;
use App\Models\User;
use Tests\TestCase;

class DealerApplicationTest extends TestCase
{
    private function ownerUser(): User
    {
        $this->withSession(['two_factor_verified' => true]);

        return User::factory()->create([
            'role' => UserRole::Owner,
            'two_factor_confirmed_at' => now(),
        ]);
    }

    private function application(array $overrides = []): DealerApplication
    {
        return DealerApplication::create(array_merge([
            'company_name' => 'Acme Tuning Ltd',
            'contact_name' => 'Jane Smith',
            'email' => 'jane@acmetuning.test',
            'phone' => '01234567890',
            'country' => 'United Kingdom',
            'message' => 'We would like to become a dealer.',
            'status' => ApplicationStatus::Pending,
            'terms_accepted_at' => now(),
        ], $overrides));
    }

    public function test_index_loads_for_owner(): void
    {
        $owner = $this->ownerUser();
        $this->application();

        $response = $this->actingAs($owner)->get('/dealer-applications');

        $response->assertOk();
    }

    public function test_index_shows_pending_applications_first(): void
    {
        $owner = $this->ownerUser();
        $this->application(['company_name' => 'Approved Co', 'status' => ApplicationStatus::Approved]);
        $this->application(['company_name' => 'Pending Co', 'status' => ApplicationStatus::Pending]);

        $response = $this->actingAs($owner)->get('/dealer-applications');

        $response->assertOk();
        $content = $response->getContent();
        $this->assertTrue(strpos($content, 'Pending Co') < strpos($content, 'Approved Co'));
    }

    public function test_index_can_filter_by_status(): void
    {
        $owner = $this->ownerUser();
        $this->application(['company_name' => 'Rejected Co', 'status' => ApplicationStatus::Rejected]);
        $this->application(['company_name' => 'Pending Co', 'status' => ApplicationStatus::Pending]);

        $response = $this->actingAs($owner)->get('/dealer-applications?status=rejected');

        $response->assertOk();
        $response->assertSee('Rejected Co');
        $response->assertDontSee('Pending Co');
    }

    public function test_show_loads_for_owner(): void
    {
        $owner = $this->ownerUser();
        $application = $this->application();

        $response = $this->actingAs($owner)->get("/dealer-applications/{$application->id}");

        $response->assertOk();
    }

    public function test_approve_creates_dealer_and_user(): void
    {
        $owner = $this->ownerUser();
        $application = $this->application();

        $response = $this->actingAs($owner)->post("/dealer-applications/{$application->id}/approve");

        $response->assertRedirect();

        $application->refresh();
        $this->assertEquals(ApplicationStatus::Approved, $application->status);
        $this->assertEquals($owner->id, $application->reviewed_by);
        $this->assertNotNull($application->reviewed_at);

        $this->assertDatabaseHas('dealers', [
            'company_name' => 'Acme Tuning Ltd',
            'country' => 'United Kingdom',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'jane@acmetuning.test',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'is_primary_contact' => true,
        ]);
    }

    public function test_reject_requires_rejection_reason(): void
    {
        $owner = $this->ownerUser();
        $application = $this->application();

        $response = $this->actingAs($owner)->post("/dealer-applications/{$application->id}/reject", []);

        $response->assertSessionHasErrors('rejection_reason');
    }

    public function test_reject_marks_application_rejected(): void
    {
        $owner = $this->ownerUser();
        $application = $this->application();

        $response = $this->actingAs($owner)->post("/dealer-applications/{$application->id}/reject", [
            'rejection_reason' => 'Insufficient trading history.',
        ]);

        $response->assertRedirect();

        $application->refresh();
        $this->assertEquals(ApplicationStatus::Rejected, $application->status);
        $this->assertEquals('Insufficient trading history.', $application->rejection_reason);
        $this->assertEquals($owner->id, $application->reviewed_by);
        $this->assertNotNull($application->reviewed_at);
    }
}
