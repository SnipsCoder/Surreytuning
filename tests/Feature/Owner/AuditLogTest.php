<?php

namespace Tests\Feature\Owner;

use App\Enums\DealerStatus;
use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\Dealer;
use App\Models\FileRequest;
use App\Models\User;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    private function ownerUser(): User
    {
        $this->withSession(['two_factor_verified' => true]);

        return User::factory()->create([
            'role' => UserRole::Owner,
            'two_factor_confirmed_at' => now(),
        ]);
    }

    public function test_adding_a_charge_records_an_audit_entry(): void
    {
        $owner = $this->ownerUser();
        $fileRequest = FileRequest::factory()->create();

        $this->actingAs($owner)->post("/file-requests/{$fileRequest->id}/charge", [
            'description' => 'ECU remap charge',
            'amount_net' => 100,
            'apply_vat' => false,
        ])->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'file_request.charge_added',
            'user_id' => $owner->id,
            'auditable_type' => FileRequest::class,
            'auditable_id' => $fileRequest->id,
            'amount' => '100.00',
            'reason' => 'ECU remap charge',
        ]);
    }

    public function test_adding_a_credit_records_an_audit_entry(): void
    {
        $owner = $this->ownerUser();
        $dealer = Dealer::factory()->create(['slave_credit_balance' => 0]);
        $fileRequest = FileRequest::factory()->create(['dealer_id' => $dealer->id]);

        $this->actingAs($owner)->post("/file-requests/{$fileRequest->id}/credit", [
            'credit_type' => 'slave',
            'amount' => 50,
            'reason' => 'Goodwill credit',
        ])->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'file_request.credit_added',
            'user_id' => $owner->id,
            'auditable_type' => FileRequest::class,
            'auditable_id' => $fileRequest->id,
            'amount' => '50.00',
            'reason' => 'Goodwill credit',
        ]);
    }

    public function test_voiding_a_file_request_records_an_audit_entry(): void
    {
        $owner = $this->ownerUser();
        $fileRequest = FileRequest::factory()->create();

        $this->actingAs($owner)->post("/file-requests/{$fileRequest->id}/void", [
            'void_reason' => 'Customer cancelled',
        ])->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'file_request.voided',
            'user_id' => $owner->id,
            'auditable_type' => FileRequest::class,
            'auditable_id' => $fileRequest->id,
            'reason' => 'Customer cancelled',
        ]);
    }

    public function test_adjusting_dealer_credits_records_an_audit_entry(): void
    {
        $owner = $this->ownerUser();
        $dealer = Dealer::factory()->create();

        $this->actingAs($owner)->post("/dealers/{$dealer->id}/credits", [
            'credit_type' => 'slave',
            'amount' => 25,
            'reason' => 'Goodwill credit',
        ])->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'dealer.credits_adjusted',
            'user_id' => $owner->id,
            'auditable_type' => Dealer::class,
            'auditable_id' => $dealer->id,
            'amount' => '25.00',
            'reason' => 'Goodwill credit',
        ]);
    }

    public function test_suspending_a_dealer_records_an_audit_entry(): void
    {
        $owner = $this->ownerUser();
        $dealer = Dealer::factory()->create(['status' => DealerStatus::Approved]);

        $this->actingAs($owner)->post("/dealers/{$dealer->id}/suspend")->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'dealer.suspended',
            'user_id' => $owner->id,
            'auditable_type' => Dealer::class,
            'auditable_id' => $dealer->id,
        ]);
    }

    public function test_reactivating_a_dealer_records_an_audit_entry(): void
    {
        $owner = $this->ownerUser();
        $dealer = Dealer::factory()->create(['status' => DealerStatus::Suspended]);

        $this->actingAs($owner)->post("/dealers/{$dealer->id}/reactivate")->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'dealer.reactivated',
            'user_id' => $owner->id,
            'auditable_type' => Dealer::class,
            'auditable_id' => $dealer->id,
        ]);
    }

    public function test_audit_record_captures_the_request_ip(): void
    {
        $owner = $this->ownerUser();
        $dealer = Dealer::factory()->create(['status' => DealerStatus::Approved]);

        $this->actingAs($owner)->post("/dealers/{$dealer->id}/suspend")->assertRedirect();

        $entry = AuditLog::where('action', 'dealer.suspended')->firstOrFail();
        $this->assertNotNull($entry->ip_address);
    }
}
