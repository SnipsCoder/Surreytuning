<?php

namespace App\Services;

use App\Enums\EvcCreditTransactionType;
use App\Enums\FileCreditTransactionType;
use App\Exceptions\InsufficientCreditsException;
use App\Models\Dealer;
use App\Models\EvcCreditTransaction;
use App\Models\FileCreditTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreditService
{
    public function addFileCredits(Dealer $dealer, float $amount, string $reason, ?User $performedBy = null, ?int $fileRequestId = null): FileCreditTransaction
    {
        return DB::transaction(function () use ($dealer, $amount, $reason, $performedBy, $fileRequestId) {
            // Lock the dealer row so concurrent credit operations serialise and
            // read a fresh balance — otherwise two transactions can both read the
            // same starting balance and one update is lost.
            $dealer = Dealer::whereKey($dealer->id)->lockForUpdate()->firstOrFail();

            $balanceAfter = bcadd((string) $dealer->file_credit_balance, (string) $amount, 2);

            $dealer->update(['file_credit_balance' => $balanceAfter]);

            return FileCreditTransaction::create([
                'dealer_id' => $dealer->id,
                'user_id' => $performedBy?->id,
                'file_request_id' => $fileRequestId,
                'type' => FileCreditTransactionType::TopUp,
                'amount' => $amount,
                'reason' => $reason,
                'balance_after' => $balanceAfter,
            ]);
        });
    }

    public function deductFileCredits(Dealer $dealer, float $amount, string $reason, User $performedBy, ?int $fileRequestId = null): FileCreditTransaction
    {
        return DB::transaction(function () use ($dealer, $amount, $reason, $performedBy, $fileRequestId) {
            // Lock the dealer row before the balance check so two concurrent
            // deductions cannot both pass the check and overspend into a negative.
            $dealer = Dealer::whereKey($dealer->id)->lockForUpdate()->firstOrFail();

            if ($dealer->file_credit_balance < $amount) {
                throw new InsufficientCreditsException('Dealer does not have sufficient file credits.');
            }

            $balanceAfter = bcsub((string) $dealer->file_credit_balance, (string) $amount, 2);

            $dealer->update(['file_credit_balance' => $balanceAfter]);

            return FileCreditTransaction::create([
                'dealer_id' => $dealer->id,
                'user_id' => $performedBy->id,
                'file_request_id' => $fileRequestId,
                'type' => FileCreditTransactionType::Deduction,
                'amount' => -$amount,
                'reason' => $reason,
                'balance_after' => $balanceAfter,
            ]);
        });
    }

    public function manualAdjustFileCredits(Dealer $dealer, float $amount, string $reason, User $performedBy): FileCreditTransaction
    {
        return DB::transaction(function () use ($dealer, $amount, $reason, $performedBy) {
            $dealer = Dealer::whereKey($dealer->id)->lockForUpdate()->firstOrFail();

            $balanceAfter = bcadd((string) $dealer->file_credit_balance, (string) $amount, 2);

            $dealer->update(['file_credit_balance' => $balanceAfter]);

            return FileCreditTransaction::create([
                'dealer_id' => $dealer->id,
                'user_id' => $performedBy->id,
                'file_request_id' => null,
                'type' => $amount >= 0 ? FileCreditTransactionType::ManualCredit : FileCreditTransactionType::Deduction,
                'amount' => $amount,
                'reason' => $reason,
                'balance_after' => $balanceAfter,
            ]);
        });
    }

    public function addEvcCredits(Dealer $dealer, float $amount, string $reason, ?User $performedBy = null, ?int $winolsBundleId = null): EvcCreditTransaction
    {
        return DB::transaction(function () use ($dealer, $amount, $reason, $performedBy, $winolsBundleId) {
            $dealer = Dealer::whereKey($dealer->id)->lockForUpdate()->firstOrFail();

            $balanceAfter = bcadd((string) $dealer->evc_credit_balance, (string) $amount, 2);

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
            $dealer = Dealer::whereKey($dealer->id)->lockForUpdate()->firstOrFail();

            if ($dealer->evc_credit_balance < $amount) {
                throw new InsufficientCreditsException('Dealer does not have sufficient EVC credits.');
            }

            $balanceAfter = bcsub((string) $dealer->evc_credit_balance, (string) $amount, 2);

            $dealer->update(['evc_credit_balance' => $balanceAfter]);

            return EvcCreditTransaction::create([
                'dealer_id' => $dealer->id,
                'user_id' => $performedBy->id,
                'winols_bundle_id' => null,
                'type' => EvcCreditTransactionType::Deduction,
                'amount' => -$amount,
                'reason' => $reason,
                'balance_after' => $balanceAfter,
            ]);
        });
    }

    public function manualAdjustEvcCredits(Dealer $dealer, float $amount, string $reason, User $performedBy): EvcCreditTransaction
    {
        return DB::transaction(function () use ($dealer, $amount, $reason, $performedBy) {
            $dealer = Dealer::whereKey($dealer->id)->lockForUpdate()->firstOrFail();

            $balanceAfter = bcadd((string) $dealer->evc_credit_balance, (string) $amount, 2);

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

    public function hasSufficientFileCredits(Dealer $dealer, float $amount): bool
    {
        return $dealer->file_credit_balance >= $amount;
    }
}
