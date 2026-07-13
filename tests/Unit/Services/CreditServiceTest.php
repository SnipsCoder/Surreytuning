<?php

namespace Tests\Unit\Services;

use App\Enums\EvcCreditTransactionType;
use App\Enums\FileCreditTransactionType;
use App\Exceptions\InsufficientCreditsException;
use App\Models\Dealer;
use App\Models\EvcCreditTransaction;
use App\Models\FileCreditTransaction;
use App\Models\User;
use App\Models\WinolsBundle;
use App\Services\CreditService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CreditServiceTest extends TestCase
{
    public function test_add_file_credits_updates_balance_and_creates_transaction(): void
    {
        $dealer = Dealer::create(['company_name' => 'Test Dealer', 'country' => 'UK', 'file_credit_balance' => 100]);
        $user = User::factory()->create();

        $transaction = (new CreditService)->addFileCredits($dealer, 50, 'Top up', $user);

        $dealer->refresh();

        $this->assertEquals(150, $dealer->file_credit_balance);
        $this->assertInstanceOf(FileCreditTransaction::class, $transaction);
        $this->assertEquals(FileCreditTransactionType::TopUp, $transaction->type);
        $this->assertEquals(50, $transaction->amount);
        $this->assertEquals(150, $transaction->balance_after);
    }

    public function test_deduct_file_credits_fails_when_insufficient_balance(): void
    {
        $dealer = Dealer::create(['company_name' => 'Test Dealer', 'country' => 'UK', 'file_credit_balance' => 10]);
        $user = User::factory()->create();

        $this->expectException(InsufficientCreditsException::class);

        (new CreditService)->deductFileCredits($dealer, 50, 'Deduction', $user);
    }

    public function test_deduct_file_credits_succeeds_and_updates_balance_correctly(): void
    {
        $dealer = Dealer::create(['company_name' => 'Test Dealer', 'country' => 'UK', 'file_credit_balance' => 100]);
        $user = User::factory()->create();

        $transaction = (new CreditService)->deductFileCredits($dealer, 30, 'Deduction', $user);

        $dealer->refresh();

        $this->assertEquals(70, $dealer->file_credit_balance);
        $this->assertEquals(FileCreditTransactionType::Deduction, $transaction->type);
        $this->assertEquals(-30, $transaction->amount);
        $this->assertEquals(70, $transaction->balance_after);
    }

    public function test_add_evc_credits_updates_balance_and_creates_transaction(): void
    {
        $dealer = Dealer::create(['company_name' => 'Test Dealer', 'country' => 'UK', 'evc_credit_balance' => 100]);
        $user = User::factory()->create();
        $bundle = WinolsBundle::create(['name' => 'Test Bundle', 'credits' => 25, 'price_net' => 100]);

        $transaction = (new CreditService)->addEvcCredits($dealer, 25, 'EVC bundle purchase', $user, $bundle->id);

        $dealer->refresh();

        $this->assertEquals(125, $dealer->evc_credit_balance);
        $this->assertInstanceOf(EvcCreditTransaction::class, $transaction);
        $this->assertEquals(EvcCreditTransactionType::Purchase, $transaction->type);
        $this->assertEquals(25, $transaction->amount);
        $this->assertEquals(125, $transaction->balance_after);
        $this->assertEquals($bundle->id, $transaction->winols_bundle_id);
    }

    public function test_deduct_evc_credits_fails_when_insufficient_balance(): void
    {
        $dealer = Dealer::create(['company_name' => 'Test Dealer', 'country' => 'UK', 'evc_credit_balance' => 10]);
        $user = User::factory()->create();

        $this->expectException(InsufficientCreditsException::class);

        (new CreditService)->deductEvcCredits($dealer, 50, 'Deduction', $user);
    }

    public function test_manual_file_adjustment_records_a_deduction_type_when_negative(): void
    {
        $dealer = Dealer::create(['company_name' => 'Test Dealer', 'country' => 'UK', 'file_credit_balance' => 100]);
        $user = User::factory()->create();

        $transaction = (new CreditService)->manualAdjustFileCredits($dealer, -40, 'Correction', $user);

        $dealer->refresh();

        $this->assertEquals(60, $dealer->file_credit_balance);
        $this->assertEquals(FileCreditTransactionType::Deduction, $transaction->type);
        $this->assertEquals(-40, $transaction->amount);
        $this->assertEquals(60, $transaction->balance_after);
    }

    public function test_db_transaction_rolls_back_on_failure(): void
    {
        $dealer = Dealer::create(['company_name' => 'Test Dealer', 'country' => 'UK', 'file_credit_balance' => 100]);
        $user = User::factory()->create();

        try {
            DB::transaction(function () use ($dealer, $user) {
                (new CreditService)->addFileCredits($dealer, 50, 'Top up', $user);
                throw new \RuntimeException('Forced failure');
            });
        } catch (\RuntimeException) {
            // expected
        }

        $dealer->refresh();

        $this->assertEquals(100, $dealer->file_credit_balance);
        $this->assertEquals(0, FileCreditTransaction::count());
    }
}
