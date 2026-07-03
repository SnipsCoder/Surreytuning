<?php

namespace Tests\Feature\Owner;

use App\Enums\UserRole;
use App\Models\Dealer;
use App\Models\FileRequest;
use App\Models\User;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    private function actingAsVerifiedOwner(): User
    {
        $owner = User::factory()->create([
            'role' => UserRole::Owner,
            'two_factor_confirmed_at' => now(),
        ]);

        $this->withSession(['two_factor_verified' => true])->actingAs($owner);

        return $owner;
    }

    public function test_dashboard_renders_for_verified_owner(): void
    {
        $this->actingAsVerifiedOwner();

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('Top Dealers')
            ->assertSee('Recent File Requests');
    }

    public function test_leaderboard_ranks_dealers_by_file_request_count(): void
    {
        $this->actingAsVerifiedOwner();

        $second = Dealer::factory()->create(['company_name' => 'Second Place Tuning']);
        $first = Dealer::factory()->create(['company_name' => 'First Place Tuning']);
        $third = Dealer::factory()->create(['company_name' => 'Third Place Tuning']);

        FileRequest::factory()->count(3)->for($second)->create();
        FileRequest::factory()->count(5)->for($first)->create();
        FileRequest::factory()->count(1)->for($third)->create();

        $this->get('/dashboard')
            ->assertOk()
            ->assertSeeInOrder([
                'First Place Tuning',
                'Second Place Tuning',
                'Third Place Tuning',
            ]);
    }

    public function test_leaderboard_excludes_dealers_with_no_file_requests(): void
    {
        $this->actingAsVerifiedOwner();

        $active = Dealer::factory()->create(['company_name' => 'Active Motors']);
        Dealer::factory()->create(['company_name' => 'Dormant Garage']);

        FileRequest::factory()->count(2)->for($active)->create();

        $response = $this->get('/dashboard')->assertOk();

        $topDealers = $response->viewData('topDealers');

        $this->assertCount(1, $topDealers);
        $this->assertSame('Active Motors', $topDealers->first()->company_name);
    }

    public function test_leaderboard_is_capped_at_eight_dealers(): void
    {
        $this->actingAsVerifiedOwner();

        Dealer::factory()
            ->count(10)
            ->create()
            ->each(fn (Dealer $dealer) => FileRequest::factory()->for($dealer)->create());

        $response = $this->get('/dashboard')->assertOk();

        $this->assertCount(8, $response->viewData('topDealers'));
    }
}
