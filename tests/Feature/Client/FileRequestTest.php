<?php

namespace Tests\Feature\Client;

use App\Enums\UserRole;
use App\Models\Dealer;
use App\Models\FileRequest;
use App\Models\User;
use Tests\TestCase;

class FileRequestTest extends TestCase
{
    private function clientUser(?Dealer $dealer = null): User
    {
        $dealer ??= Dealer::factory()->create();

        return User::factory()->create([
            'role' => UserRole::DealerOwner,
            'dealer_id' => $dealer->id,
        ]);
    }

    public function test_dashboard_loads_for_client(): void
    {
        $user = $this->clientUser();

        $response = $this->actingAs($user)->get('/my/dashboard');

        $response->assertOk();
    }

    public function test_index_shows_only_own_dealer_requests(): void
    {
        $dealer = Dealer::factory()->create();
        $user = $this->clientUser($dealer);

        $own = FileRequest::factory()->create(['dealer_id' => $dealer->id]);
        $other = FileRequest::factory()->create();

        $response = $this->actingAs($user)->get('/my/file-requests');

        $response->assertOk();
        $response->assertSee($own->request_number_formatted);
        $response->assertDontSee($other->request_number_formatted);
    }

    public function test_client_cannot_view_another_dealers_file_request(): void
    {
        $user = $this->clientUser();
        $other = FileRequest::factory()->create();

        $response = $this->actingAs($user)->get("/my/file-requests/{$other->id}");

        $response->assertForbidden();
    }

    public function test_client_can_view_own_file_request(): void
    {
        $dealer = Dealer::factory()->create();
        $user = $this->clientUser($dealer);
        $fileRequest = FileRequest::factory()->create(['dealer_id' => $dealer->id]);

        $response = $this->actingAs($user)->get("/my/file-requests/{$fileRequest->id}");

        $response->assertOk();
    }
}
