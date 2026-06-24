<?php

namespace Tests\Unit\Services;

use App\Enums\SlaveCreditTransactionType;
use App\Exceptions\InsufficientCreditsException;
use App\Models\Dealer;
use App\Models\SlaveCreditTransaction;
use App\Models\User;
use App\Services\CreditService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CreditServiceTest extends TestCase
{
    public function test_add_slave_credits_updates_balance_and_creates_transaction(): void
    {
        $dealer = Dealer::create(['company_name' => 'Test Dealer', 'country' => 'UK', 'slave_credit_balance' => 100]);
        $user = User::factory()->create();

        $transaction = (new CreditService)->addSlaveCredits($dealer, 50, 'Top up', $user);

        $dealer->refresh();

        $this->assertEquals(150, $dealer->slave_credit_balance);
        $this->assertInstanceOf(SlaveCreditTransaction::class, $transaction);
        $this->assertEquals(SlaveCreditTransactionType::TopUp, $transaction->type);
        $this->assertEquals(50, $transaction->amount);
        $this->assertEquals(150, $transaction->balance_after);
    }

    public function test_deduct_slave_credits_fails_when_insufficient_balance(): void
    {
        $dealer = Dealer::create(['company_name' => 'Test Dealer', 'country' => 'UK', 'slave_credit_balance' => 10]);
        $user = User::factory()->create();

        $this->expectException(InsufficientCreditsException::class);

        (new CreditService)->deductSlaveCredits($dealer, 50, 'Deduction', $user);
    }

    public function test_deduct_slave_credits_succeeds_and_updates_balance_correctly(): void
    {
        $dealer = Dealer::create(['company_name' => 'Test Dealer', 'country' => 'UK', 'slave_credit_balance' => 100]);
        $user = User::factory()->create();

        $transaction = (new CreditService)->deductSlaveCredits($dealer, 30, 'Deduction', $user);

        $dealer->refresh();

        $this->assertEquals(70, $dealer->slave_credit_balance);
        $this->assertEquals(SlaveCreditTransactionType::Deduction, $transaction->type);
        $this->assertEquals(-30, $transaction->amount);
        $this->assertEquals(70, $transaction->balance_after);
    }

    public function test_db_transaction_rolls_back_on_failure(): void
    {
        $dealer = Dealer::create(['company_name' => 'Test Dealer', 'country' => 'UK', 'slave_credit_balance' => 100]);
        $user = User::factory()->create();

        try {
            DB::transaction(function () use ($dealer, $user) {
                (new CreditService)->addSlaveCredits($dealer, 50, 'Top up', $user);
                throw new \RuntimeException('Forced failure');
            });
        } catch (\RuntimeException) {
            // expected
        }

        $dealer->refresh();

        $this->assertEquals(100, $dealer->slave_credit_balance);
        $this->assertEquals(0, SlaveCreditTransaction::count());
    }
}
