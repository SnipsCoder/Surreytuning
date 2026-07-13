<?php

namespace Tests\Feature\Owner;

use App\Enums\FileRequestStatus;
use App\Enums\UserRole;
use App\Models\Dealer;
use App\Models\FileRequest;
use App\Models\Invoice;
use App\Models\User;
use Tests\TestCase;

class FileRequestTest extends TestCase
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
        FileRequest::factory()->count(3)->create();

        $response = $this->actingAs($owner)->get('/file-requests');

        $response->assertOk();
    }

    public function test_show_loads_for_owner(): void
    {
        $owner = $this->ownerUser();
        $fileRequest = FileRequest::factory()->create();

        $response = $this->actingAs($owner)->get("/file-requests/{$fileRequest->id}");

        $response->assertOk();
    }

    public function test_status_can_be_updated(): void
    {
        $owner = $this->ownerUser();
        $fileRequest = FileRequest::factory()->create(['status' => FileRequestStatus::Pending]);

        $response = $this->actingAs($owner)->post("/file-requests/{$fileRequest->id}/status", [
            'status' => FileRequestStatus::Progress->value,
        ]);

        $response->assertRedirect();
        $this->assertEquals(FileRequestStatus::Progress, $fileRequest->refresh()->status);
    }

    public function test_add_charge_creates_invoice(): void
    {
        $owner = $this->ownerUser();
        $fileRequest = FileRequest::factory()->create();

        $response = $this->actingAs($owner)->post("/file-requests/{$fileRequest->id}/charge", [
            'description' => 'ECU remap charge',
            'amount_net' => 100,
            'apply_vat' => false,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('invoices', [
            'dealer_id' => $fileRequest->dealer_id,
            'related_type' => FileRequest::class,
            'related_id' => $fileRequest->id,
        ]);
        $this->assertSame(1, Invoice::count());
    }

    public function test_add_credit_updates_dealer_balance(): void
    {
        $owner = $this->ownerUser();
        $dealer = Dealer::factory()->create(['file_credit_balance' => 0]);
        $fileRequest = FileRequest::factory()->create(['dealer_id' => $dealer->id]);

        $response = $this->actingAs($owner)->post("/file-requests/{$fileRequest->id}/credit", [
            'credit_type' => 'file',
            'amount' => 50,
            'reason' => 'Goodwill credit',
        ]);

        $response->assertRedirect();
        $this->assertEquals(50, $dealer->refresh()->file_credit_balance);
    }

    public function test_void_closes_file_request(): void
    {
        $owner = $this->ownerUser();
        $fileRequest = FileRequest::factory()->create();

        $response = $this->actingAs($owner)->post("/file-requests/{$fileRequest->id}/void", [
            'void_reason' => 'Customer cancelled',
        ]);

        $response->assertRedirect();
        $fileRequest->refresh();
        $this->assertEquals(FileRequestStatus::Void, $fileRequest->status);
        $this->assertNotNull($fileRequest->closed_at);
    }
}
