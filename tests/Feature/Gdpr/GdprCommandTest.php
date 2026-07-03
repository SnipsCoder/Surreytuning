<?php

namespace Tests\Feature\Gdpr;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Models\Dealer;
use App\Models\DealerApplication;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GdprCommandTest extends TestCase
{
    public function test_export_command_writes_a_package_to_disk(): void
    {
        Storage::fake('local');

        $dealer = Dealer::factory()->create();
        User::factory()->create(['dealer_id' => $dealer->id]);

        $this->artisan('gdpr:export', ['dealer' => $dealer->id])
            ->assertSuccessful();

        $files = Storage::disk('local')->files(config('gdpr.export_path'));

        $this->assertCount(1, $files);
        $this->assertStringContainsString("dealer-{$dealer->id}-", $files[0]);
    }

    public function test_export_command_fails_for_an_unknown_dealer(): void
    {
        $this->artisan('gdpr:export', ['dealer' => 999999])
            ->assertFailed();
    }

    public function test_erase_command_anonymises_the_dealer(): void
    {
        $dealer = Dealer::factory()->create();
        User::factory()->create(['dealer_id' => $dealer->id]);

        $this->artisan('gdpr:erase', ['dealer' => $dealer->id, '--force' => true])
            ->assertSuccessful();

        $this->assertNull(Dealer::find($dealer->id));
        $this->assertNotNull(Dealer::withTrashed()->find($dealer->id)->deleted_at);
    }

    public function test_erase_command_fails_for_an_unknown_dealer(): void
    {
        $this->artisan('gdpr:erase', ['dealer' => 999999, '--force' => true])
            ->assertFailed();
    }

    public function test_prune_removes_expired_data(): void
    {
        $retentionDays = config('gdpr.retention.rejected_applications_days');

        // Old rejected application — should be pruned.
        $old = DealerApplication::create([
            'company_name' => 'Old Reject',
            'contact_name' => 'Old',
            'email' => 'old@reject.test',
            'country' => 'United Kingdom',
            'status' => ApplicationStatus::Rejected->value,
        ]);
        $old->forceFill(['created_at' => now()->subDays($retentionDays + 10)])->save();

        // Recent rejected application — should be kept.
        DealerApplication::create([
            'company_name' => 'Recent Reject',
            'contact_name' => 'Recent',
            'email' => 'recent@reject.test',
            'country' => 'United Kingdom',
            'status' => ApplicationStatus::Rejected->value,
        ]);

        // Approved application, old — must never be pruned.
        $approved = DealerApplication::create([
            'company_name' => 'Approved',
            'contact_name' => 'Approved',
            'email' => 'approved@customer.test',
            'country' => 'United Kingdom',
            'status' => ApplicationStatus::Approved->value,
        ]);
        $approved->forceFill(['created_at' => now()->subDays($retentionDays + 10)])->save();

        // User with an expired OTP — the code must be cleared.
        $user = User::factory()->create([
            'role' => UserRole::DealerUser->value,
            'email_otp_code' => '654321',
            'email_otp_expires_at' => now()->subHour(),
        ]);

        $this->artisan('gdpr:prune')->assertSuccessful();

        $this->assertNull(DealerApplication::find($old->id));
        $this->assertSame(2, DealerApplication::count());
        $this->assertNotNull(DealerApplication::find($approved->id));
        $this->assertNull($user->fresh()->email_otp_code);
    }

    public function test_prune_dry_run_deletes_nothing(): void
    {
        $retentionDays = config('gdpr.retention.rejected_applications_days');

        $old = DealerApplication::create([
            'company_name' => 'Old Reject',
            'contact_name' => 'Old',
            'email' => 'old@reject.test',
            'country' => 'United Kingdom',
            'status' => ApplicationStatus::Rejected->value,
        ]);
        $old->forceFill(['created_at' => now()->subDays($retentionDays + 10)])->save();

        $user = User::factory()->create([
            'role' => UserRole::DealerUser->value,
            'email_otp_code' => '654321',
            'email_otp_expires_at' => now()->subHour(),
        ]);

        $this->artisan('gdpr:prune', ['--dry-run' => true])->assertSuccessful();

        $this->assertNotNull(DealerApplication::find($old->id));
        $this->assertSame('654321', $user->fresh()->email_otp_code);
    }
}
