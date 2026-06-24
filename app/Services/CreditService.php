<?php

namespace App\Services;

use App\Enums\EvcCreditTransactionType;
use App\Enums\SlaveCreditTransactionType;
use App\Exceptions\InsufficientCreditsException;
use App\Models\Dealer;
use App\Models\EvcCreditTransaction;
use App\Models\SlaveCreditTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreditService
{
    public function addSlaveCredits(Dealer $dealer, float $amount, string $reason, ?User $performedBy = null, ?int $fileRequestId = null): SlaveCreditTransaction
    {
        return DB::transaction(function () use ($dealer, $amount, $reason, $performedBy, $fileRequestId) {
            $balanceAfter = $dealer->slave_credit_balance + $amount;

            $dealer->update(['slave_credit_balance' => $balanceAfter]);

            return SlaveCreditTransaction::create([
                'dealer_id' => $dealer->id,
                'user_id' => $performedBy?->id,
                'file_request_id' => $fileRequestId,
                'type' => SlaveCreditTransactionType::TopUp,
                'amount' => $amount,
                'reason' => $reason,
                'balance_after' => $balanceAfter,
            ]);
        });
    }

    public function deductSlaveCredits(Dealer $dealer, float $amount, string $reason, User $performedBy, ?int $fileRequestId = null): SlaveCreditTransaction
    {
        return DB::transaction(function () use ($dealer, $amount, $reason, $performedBy, $fileRequestId) {
            if ($dealer->slave_credit_balance < $amount) {
                throw new InsufficientCreditsException('Dealer does not have sufficient slave credits.');
            }

            $balanceAfter = $dealer->slave_credit_balance - $amount;

            $dealer->update(['slave_credit_balance' => $balanceAfter]);

            return SlaveCreditTransaction::create([
                'dealer_id' => $dealer->id,
                'user_id' => $performedBy->id,
                'file_request_id' => $fileRequestId,
                'type' => SlaveCreditTransactionType::Deduction,
                'amount' => -$amount,
                'reason' => $reason,
                'balance_after' => $balanceAfter,
            ]);
        });
    }

    public function manualAdjustSlaveCredits(Dealer $dealer, float $amount, string $reason, User $performedBy): SlaveCreditTransaction
    {
        return DB::transaction(function () use ($dealer, $amount, $reason, $performedBy) {
            $balanceAfter = $dealer->slave_credit_balance + $amount;

            $dealer->update(['slave_credit_balance' => $balanceAfter]);

            return SlaveCreditTransaction::create([
                'dealer_id' => $dealer->id,
                'user_id' => $performedBy->id,
                'file_request_id' => null,
                'type' => $amount >= 0 ? SlaveCreditTransactionType::ManualCredit : SlaveCreditTransactionType::Deduction,
                'amount' => $amount,
                'reason' => $reason,
                'balance_after' => $balanceAfter,
            ]);
        });
    }

    public function addEvcCredits(Dealer $dealer, float $amount, string $reason, ?User $performedBy = null, ?int $winolsBundleId = null): EvcCreditTransaction
    {
        return DB::transaction(function () use ($dealer, $amount, $reason, $performedBy, $winolsBundleId) {
            $balanceAfter = $dealer->evc_credit_balance + $amount;

            $dealer->update(['evc_credit_balance' => $balanceAfter]);

            return EvcCreditTransaction::create([
                'dealer_id' => $dealer->id,
                'user_id' => $performedBy?->id,
                'winols_bundle_id' => $winolsBundleId,
                'type' => EvcCreditTransactionType::Purchase,
                'amount' => $amount,
                'reason' => $reason,
                'balance_after' => $balanceAfter,
            ]);
        });
    }

    public function deductEvcCredits(Dealer $dealer, float $amount, string $reason, User $performedBy): EvcCreditTransaction
    {
        return DB::transaction(function () use ($dealer, $amount, $reason, $performedBy) {
            if ($dealer->evc_credit_balance < $amount) {
                throw new InsufficientCreditsException('Dealer does not have sufficient EVC credits.');
            }

            $balanceAfter = $dealer->evc_credit_balance - $amount;

            $dealer->update(['evc_credit_balance' => $balanceAfter]);

            return EvcCreditTransaction::create([
                'dealer_id' => $dealer->id,
                'user_id' => $performedBy->id,
                'winols_bundle_id' => null,
                'type' => EvcCreditTransactionType::ManualCredit,
                'amount' => -$amount,
                'reason' => $reason,
                'balance_after' => $balanceAfter,
            ]);
        });
    }

    public function manualAdjustEvcCredits(Dealer $dealer, float $amount, string $reason, User $performedBy): EvcCreditTransaction
    {
        return DB::transaction(function () use ($dealer, $amount, $reason, $performedBy) {
            $balanceAfter = $dealer->evc_credit_balance + $amount;

            $dealer->update(['evc_credit_balance' => $balanceAfter]);

            return EvcCreditTransaction::create([
                'dealer_id' => $dealer->id,
                'user_id' => $performedBy->id,
                'winols_bundle_id' => null,
                'type' => EvcCreditTransactionType::ManualCredit,
                'amount' => $amount,
                'reason' => $reason,
                'balance_after' => $balanceAfter,
            ]);
        });
    }

    public function hasSufficientSlaveCredits(Dealer $dealer, float $amount): bool
    {
        return $dealer->slave_credit_balance >= $amount;
    }
}
