<?php

namespace Tests\Feature\Owner;

use App\Enums\DealerStatus;
use App\Enums\UserRole;
use App\Models\Dealer;
use App\Models\User;
use Tests\TestCase;

class DealerTest extends TestCase
{
    private function ownerUser(): User
    {
        $this->withSession(['two_factor_verified' => true]);

        return User::factory()->create([
            'role' => UserRole::Owner,
            'two_factor_confirmed_at' => now(),
        ]);
    }

    public function test_index_loads_for_owner(): void
    {
        $owner = $this->ownerUser();
        Dealer::factory()->count(3)->create();

        $response = $this->actingAs($owner)->get('/dealers');

        $response->assertOk();
    }

    public function test_index_can_search_by_company_name(): void
    {
        $owner = $this->ownerUser();
        Dealer::factory()->create(['company_name' => 'Acme Tuning Ltd']);
        Dealer::factory()->create(['company_name' => 'Other Dealer']);

        $response = $this->actingAs($owner)->get('/dealers?search=Acme');

        $response->assertOk();
        $response->assertSee('Acme Tuning Ltd');
        $response->assertDontSee('Other Dealer');
    }

    public function test_index_can_filter_by_status(): void
    {
        $owner = $this->ownerUser();
        Dealer::factory()->create(['company_name' => 'Suspended Dealer', 'status' => DealerStatus::Suspended]);
        Dealer::factory()->create(['company_name' => 'Approved Dealer', 'status' => DealerStatus::Approved]);

        $response = $this->actingAs($owner)->get('/dealers?status=suspended');

        $response->assertOk();
        $response->assertSee('Suspended Dealer');
        $response->assertDontSee('Approved Dealer');
    }

    public function test_show_loads_for_owner(): void
    {
        $owner = $this->ownerUser();
        $dealer = Dealer::factory()->create();

        $response = $this->actingAs($owner)->get("/dealers/{$dealer->id}");

        $response->assertOk();
    }

    public function test_notes_can_be_updated(): void
    {
        $owner = $this->ownerUser();
        $dealer = Dealer::factory()->create();

        $response = $this->actingAs($owner)->patch("/dealers/{$dealer->id}", [
            'notes' => 'Spoke with dealer about late payment.',
        ]);

        $response->assertRedirect();
        $this->assertSame('Spoke with dealer about late payment.', $dealer->refresh()->notes);
    }

    public function test_file_credits_can_be_adjusted(): void
    {
        $owner = $this->ownerUser();
        $dealer = Dealer::factory()->create(['file_credit_balance' => 10]);

        $response = $this->actingAs($owner)->post("/dealers/{$dealer->id}/credits", [
            'credit_type' => 'file',
            'amount' => 25,
            'reason' => 'Goodwill credit',
        ]);

        $response->assertRedirect();
        $this->assertEquals(35, $dealer->refresh()->file_credit_balance);
        $this->assertDatabaseHas('file_credit_transactions', [
            'dealer_id' => $dealer->id,
            'reason' => 'Goodwill credit',
        ]);
    }

    public function test_evc_credits_can_be_adjusted(): void
    {
        $owner = $this->ownerUser();
        $dealer = Dealer::factory()->create(['evc_credit_balance' => 5]);

        $response = $this->actingAs($owner)->post("/dealers/{$dealer->id}/credits", [
            'credit_type' => 'evc',
            'amount' => -2,
            'reason' => 'Correction',
        ]);

        $response->assertRedirect();
        $this->assertEquals(3, $dealer->refresh()->evc_credit_balance);
        $this->assertDatabaseHas('evc_credit_transactions', [
            'dealer_id' => $dealer->id,
            'reason' => 'Correction',
        ]);
    }

    public function test_dealer_can_be_suspended(): void
    {
        $owner = $this->ownerUser();
        $dealer = Dealer::factory()->create(['status' => DealerStatus::Approved]);

        $response = $this->actingAs($owner)->post("/dealers/{$dealer->id}/suspend");

        $response->assertRedirect();
        $this->assertEquals(DealerStatus::Suspended, $dealer->refresh()->status);
    }

    public function test_dealer_can_be_reactivated(): void
    {
        $owner = $this->ownerUser();
        $dealer = Dealer::factory()->create(['status' => DealerStatus::Suspended]);

        $response = $this->actingAs($owner)->post("/dealers/{$dealer->id}/reactivate");

        $response->assertRedirect();
        $this->assertEquals(DealerStatus::Approved, $dealer->refresh()->status);
    }
}
