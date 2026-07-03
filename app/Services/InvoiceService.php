<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Models\Dealer;
use App\Models\Invoice;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function createInvoice(Dealer $dealer, string $description, float $amountNet, InvoiceType $type, ?User $raisedBy = null, ?int $relatedId = null, ?string $relatedType = null, bool $applyVat = true): Invoice
    {
        return DB::transaction(function () use ($dealer, $description, $amountNet, $type, $raisedBy, $relatedId, $relatedType, $applyVat) {
            $settings = Setting::get();

            $vatAmount = $applyVat ? round($amountNet * ($settings->vat_rate / 100), 2) : 0;
            $amountGross = $amountNet + $vatAmount;

            $invoiceNumber = $settings->invoice_start_number + (Invoice::lockForUpdate()->max('invoice_number') ?? 0);

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
