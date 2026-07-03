<?php

namespace Tests\Feature\Gdpr;

use App\Enums\ApplicationStatus;
use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\Dealer;
use App\Models\DealerApplication;
use App\Models\Invoice;
use App\Models\User;
use App\Services\DataSubjectService;
use Tests\TestCase;

class DataSubjectServiceTest extends TestCase
{
    private function makeDealerWithData(): Dealer
    {
        $dealer = Dealer::factory()->create([
            'company_name' => 'Precision Remaps Ltd',
            'invoice_address' => '1 High Street',
            'notes' => 'VIP customer',
        ]);

        $user = User::factory()->create([
            'dealer_id' => $dealer->id,
            'email' => 'owner@precision.test',
            'role' => UserRole::DealerOwner->value,
            'is_primary_contact' => true,
            'two_factor_secret' => 'super-secret-seed',
            'email_otp_code' => '123456',
        ]);

        DealerApplication::create([
            'company_name' => 'Precision Remaps Ltd',
            'contact_name' => 'Jane Doe',
            'email' => 'owner@precision.test',
            'phone' => '07000000000',
            'country' => 'United Kingdom',
            'message' => 'Please approve us',
            'status' => ApplicationStatus::Approved->value,
        ]);

        Invoice::create([
            'dealer_id' => $dealer->id,
            'user_id' => $user->id,
            'invoice_number' => 5001,
            'description' => 'Credit top-up',
            'amount_net' => 100.00,
            'vat_amount' => 20.00,
            'amount_gross' => 120.00,
            'type' => InvoiceType::CreditTopUp->value,
            'status' => InvoiceStatus::Paid->value,
        ]);

        return $dealer;
    }

    public function test_export_builds_a_complete_package(): void
    {
        $dealer = $this->makeDealerWithData();

        $package = app(DataSubjectService::class)->export($dealer);

        $this->assertArrayHasKey('exported_at', $package);
        $this->assertSame('Precision Remaps Ltd', $package['dealer']['company_name']);
        $this->assertCount(1, $package['users']);
        $this->assertSame('owner@precision.test', $package['users'][0]['email']);
        $this->assertCount(1, $package['applications']);
        $this->assertCount(1, $package['invoices']);
        $this->assertSame(5001, $package['invoices'][0]['invoice_number']);
    }

    public function test_export_never_leaks_secrets(): void
    {
        $dealer = $this->makeDealerWithData();

        $package = app(DataSubjectService::class)->export($dealer);
        $json = json_encode($package);

        $this->assertStringNotContainsString('super-secret-seed', $json);
        $this->assertStringNotContainsString('123456', $json);
        $this->assertArrayNotHasKey('password', $package['users'][0]);
        $this->assertArrayNotHasKey('two_factor_secret', $package['users'][0]);
        $this->assertArrayNotHasKey('email_otp_code', $package['users'][0]);
    }

    public function test_erase_anonymises_and_soft_deletes_the_subject(): void
    {
        $dealer = $this->makeDealerWithData();
        $userId = $dealer->users()->first()->id;

        $counts = app(DataSubjectService::class)->erase($dealer, null, 'Subject request');

        $this->assertSame(['dealers' => 1, 'users' => 1, 'applications' => 1], $counts);

        // Dealer is anonymised + soft-deleted.
        $freshDealer = Dealer::withTrashed()->find($dealer->id);
        $this->assertNotNull($freshDealer->deleted_at);
        $this->assertSame('Erased dealer #'.$dealer->id, $freshDealer->company_name);
        $this->assertNull($freshDealer->invoice_address);
        $this->assertNull($freshDealer->notes);

        // User is anonymised + soft-deleted, secrets cleared.
        $freshUser = User::withTrashed()->find($userId);
        $this->assertNotNull($freshUser->deleted_at);
        $this->assertSame('Erased', $freshUser->first_name);
        $this->assertSame("erased-user-{$userId}@gdpr.invalid", $freshUser->email);
        $this->assertNull($freshUser->two_factor_secret);
        $this->assertNull($freshUser->email_otp_code);

        // Application anonymised.
        $application = DealerApplication::first();
        $this->assertSame('Erased (GDPR)', $application->company_name);
        $this->assertNull($application->phone);
    }

    public function test_erase_retains_financial_records(): void
    {
        $dealer = $this->makeDealerWithData();

        app(DataSubjectService::class)->erase($dealer);

        // Invoice is retained for the statutory accounting period.
        $this->assertSame(1, Invoice::where('dealer_id', $dealer->id)->count());
    }

    public function test_erase_writes_an_audit_log_entry(): void
    {
        $dealer = $this->makeDealerWithData();
        $actor = User::factory()->create(['role' => UserRole::Owner->value]);

        app(DataSubjectService::class)->erase($dealer, $actor, 'Right to be forgotten');

        $log = AuditLog::where('action', 'gdpr_erased')->first();

        $this->assertNotNull($log);
        $this->assertSame($actor->id, $log->user_id);
        $this->assertSame('Right to be forgotten', $log->reason);
        $this->assertSame(1, $log->metadata['users']);
    }
}
