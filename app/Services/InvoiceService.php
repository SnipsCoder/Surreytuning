<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Models\Dealer;
use App\Models\Invoice;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function createInvoice(Dealer $dealer, string $description, float $amountNet, InvoiceType $type, ?User $raisedBy = null, ?int $relatedId = null, ?string $relatedType = null, bool $applyVat = true): Invoice
    {
        // Retry on a duplicate invoice_number: concurrent callers can read the
        // same MAX() before either commits, and the UNIQUE constraint rejects the
        // loser. When nested inside an outer transaction (e.g. the Stripe webhook
        // handler) the inner transaction is a savepoint; a duplicate-key error is
        // statement-level in InnoDB, so the surrounding transaction stays usable
        // and the retry can recompute the next number safely.
        for ($attempt = 1; ; $attempt++) {
            try {
                return $this->createInvoiceOnce($dealer, $description, $amountNet, $type, $raisedBy, $relatedId, $relatedType, $applyVat);
            } catch (QueryException $e) {
                // SQLSTATE 23000 / 23505 = integrity constraint (duplicate key).
                $isDuplicate = in_array((string) $e->getCode(), ['23000', '23505'], true);

                if ($attempt >= 3 || ! $isDuplicate) {
                    throw $e;
                }
            }
        }
    }

    private function createInvoiceOnce(Dealer $dealer, string $description, float $amountNet, InvoiceType $type, ?User $raisedBy, ?int $relatedId, ?string $relatedType, bool $applyVat): Invoice
    {
        return DB::transaction(function () use ($dealer, $description, $amountNet, $type, $raisedBy, $relatedId, $relatedType, $applyVat) {
            $settings = Setting::get();

            $vatAmount = $applyVat ? round($amountNet * ($settings->vat_rate / 100), 2) : 0;
            $amountGross = $amountNet + $vatAmount;

            // The start number is a floor for the very first invoice only.
            // Thereafter we increment the highest existing number by one.
            // (The previous `start_number + max()` compounded the offset on
            // every invoice — 10000, then 20000, then 30000 — and collided
            // with the UNIQUE constraint once gaps appeared.)
            $currentMax = Invoice::lockForUpdate()->max('invoice_number');

            $invoiceNumber = $currentMax !== null
                ? $currentMax + 1
                : $settings->invoice_start_number;

            return Invoice::create([
                'dealer_id' => $dealer->id,
                'user_id' => $raisedBy?->id,
                'invoice_number' => $invoiceNumber,
                'description' => $description,
                'amount_net' => $amountNet,
                'vat_amount' => $vatAmount,
                'amount_gross' => $amountGross,
                'type' => $type,
                'related_id' => $relatedId,
                'related_type' => $relatedType,
                'status' => InvoiceStatus::Issued,
            ]);
        });
    }

    public function markPaid(Invoice $invoice, ?string $stripePaymentIntentId = null): Invoice
    {
        $invoice->update([
            'status' => InvoiceStatus::Paid,
            'paid_at' => now(),
            'stripe_payment_intent_id' => $stripePaymentIntentId ?? $invoice->stripe_payment_intent_id,
        ]);

        return $invoice;
    }

    public function voidInvoice(Invoice $invoice): Invoice
    {
        $invoice->update(['status' => InvoiceStatus::Void]);

        return $invoice;
    }
}
