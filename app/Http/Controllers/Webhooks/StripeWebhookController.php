<?php

namespace App\Http\Controllers\Webhooks;

use App\Enums\InvoiceType;
use App\Events\PaymentConfirmed;
use App\Http\Controllers\Controller;
use App\Models\Dealer;
use App\Models\Invoice;
use App\Models\ProcessedStripeEvent;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\User;
use App\Models\WinolsBundle;
use App\Services\CreditService;
use App\Services\EvcService;
use App\Services\InvoiceService;
use App\Services\StripeService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Throwable;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    public function __construct(
        private StripeService $stripeService,
        private CreditService $creditService,
        private InvoiceService $invoiceService,
        private EvcService $evcService,
    ) {}

    public function handle(Request $request)
    {
        try {
            $event = $this->stripeService->constructWebhookEvent(
                $request->getContent(),
                $request->header('Stripe-Signature') ?? '',
            );
        } catch (UnexpectedValueException|SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature verification failed', ['error' => $e->getMessage()]);

            return response('Invalid signature', 400);
        }

        Log::info('Stripe webhook received', ['type' => $event->type, 'event_id' => $event->id, 'created_at' => $event->created]);

        // Event-level idempotency. Stripe may redeliver the same event (retries
        // on non-2xx, at-least-once delivery), so we claim the event id before
        // running any handler. The unique constraint makes this atomic: if the
        // event was already processed — including by a concurrent request that
        // won the insert race — we acknowledge with 200 and do nothing. This is
        // the first line of defence; the per-payment-intent guard in each
        // handler remains as a second layer.
        if (ProcessedStripeEvent::where('event_id', $event->id)->exists()) {
            Log::info('Stripe webhook: duplicate event skipped (already processed)', ['event_id' => $event->id]);

            return response('Webhook already handled', 200);
        }

        try {
            ProcessedStripeEvent::create(['event_id' => $event->id, 'type' => $event->type]);
        } catch (QueryException $e) {
            // Lost the insert race to a concurrent delivery of the same event.
            Log::info('Stripe webhook: duplicate event skipped (concurrent delivery)', ['event_id' => $event->id]);

            return response('Webhook already handled', 200);
        }

        try {
            if ($event->type === 'checkout.session.completed') {
                $session = $event->data->object;
                $metadata = $session->metadata;
                $paymentIntentId = $session->payment_intent;

                match ($metadata->type ?? null) {
                    // Accept both the legacy 'slave_credits' and the new 'file_credits'
                    // type so checkout sessions created before this rename still complete.
                    'slave_credits', 'file_credits' => $this->handleFileCredits($metadata, $paymentIntentId),
                    'evc_bundle' => $this->handleEvcBundle($metadata, $paymentIntentId),
                    'product' => $this->handleProduct($metadata, $paymentIntentId),
                    'invoice' => $this->handleInvoice($metadata, $paymentIntentId),
                    default => Log::warning('Stripe webhook: unrecognised metadata type', ['metadata' => (array) $metadata]),
                };
            } elseif ($event->type === 'payment_intent.payment_failed') {
                $paymentIntent = $event->data->object;
                Log::error('Stripe payment failed', [
                    'payment_intent_id' => $paymentIntent->id,
                    'last_error' => $paymentIntent->last_payment_error?->message,
                ]);
            }
        } catch (Throwable $e) {
            Log::error('Stripe webhook handler threw an exception', [
                'event_type' => $event->type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Release the idempotency claim so Stripe's automatic retry can
            // reprocess this event from scratch. Each handler is atomic (its
            // credit + invoice mutations run in one transaction), so nothing
            // partial was committed and reprocessing will not double-apply.
            // Returning a non-2xx status is what tells Stripe to retry.
            ProcessedStripeEvent::where('event_id', $event->id)->delete();

            return response('Webhook handler failed', 500);
        }

        return response('Webhook handled', 200);
    }

    private function handleFileCredits(object $metadata, ?string $paymentIntentId): void
    {
        if ($paymentIntentId && Invoice::where('stripe_payment_intent_id', $paymentIntentId)->exists()) {
            Log::info('Stripe webhook: duplicate file_credits event skipped', ['payment_intent_id' => $paymentIntentId]);

            return;
        }

        $dealer = Dealer::findOrFail($metadata->dealer_id);
        $user = User::findOrFail($metadata->user_id);
        $product = Product::findOrFail($metadata->product_id);

        // The dealer receives credits at full face value; the discount only
        // reduces the money they pay, so it applies to the invoice net.
        $creditAmount = (float) $product->price_net;
        $invoiceAmount = $dealer->discountedPrice((float) $product->price_net);

        $invoice = DB::transaction(function () use ($dealer, $user, $product, $creditAmount, $invoiceAmount, $paymentIntentId) {
            $this->creditService->addFileCredits(
                $dealer,
                $creditAmount,
                "File credit top-up: {$product->name}",
                $user,
            );

            $invoice = $this->invoiceService->createInvoice(
                $dealer,
                "File credit top-up: {$product->name}",
                $invoiceAmount,
                InvoiceType::CreditTopUp,
                $user,
            );

            return $this->invoiceService->markPaid($invoice, $paymentIntentId);
        });

        PaymentConfirmed::dispatch($invoice, $dealer);
    }

    private function handleEvcBundle(object $metadata, ?string $paymentIntentId): void
    {
        if ($paymentIntentId && Invoice::where('stripe_payment_intent_id', $paymentIntentId)->exists()) {
            Log::info('Stripe webhook: duplicate evc_bundle event skipped', ['payment_intent_id' => $paymentIntentId]);

            return;
        }

        $dealer = Dealer::findOrFail($metadata->dealer_id);
        $user = User::findOrFail($metadata->user_id);
        $bundle = WinolsBundle::findOrFail($metadata->winols_bundle_id);

        $invoice = DB::transaction(function () use ($dealer, $user, $bundle, $paymentIntentId) {
            $this->creditService->addEvcCredits(
                $dealer,
                (float) $bundle->credits,
                "EVC bundle purchase: {$bundle->name}",
                $user,
                $bundle->id,
            );

            $invoice = $this->invoiceService->createInvoice(
                $dealer,
                "EVC bundle purchase: {$bundle->name}",
                $dealer->discountedPrice((float) $bundle->price_net),
                InvoiceType::EvcBundle,
                $user,
                $bundle->id,
                WinolsBundle::class,
            );

            return $this->invoiceService->markPaid($invoice, $paymentIntentId);
        });

        // Push the purchased credits onto the dealer's real EVC account. Done
        // outside the DB transaction and never allowed to fail the purchase —
        // if the reseller API isn't wired yet it just logs for manual action.
        $this->evcService->allocateCredits($dealer, (float) $bundle->credits, "EVC bundle purchase: {$bundle->name}");

        PaymentConfirmed::dispatch($invoice, $dealer);
    }

    private function handleProduct(object $metadata, ?string $paymentIntentId): void
    {
        if ($paymentIntentId && Invoice::where('stripe_payment_intent_id', $paymentIntentId)->exists()) {
            Log::info('Stripe webhook: duplicate product event skipped', ['payment_intent_id' => $paymentIntentId]);

            return;
        }

        $order = ProductOrder::findOrFail($metadata->product_order_id);
        $dealer = Dealer::findOrFail($order->dealer_id);
        $user = User::findOrFail($order->user_id);
        $product = $order->product;

        $invoice = DB::transaction(function () use ($order, $dealer, $user, $product, $paymentIntentId) {
            $order->update([
                'status' => 'paid',
                'stripe_payment_intent_id' => $paymentIntentId,
            ]);

            // Draw down stock now that payment has completed (NULL = unlimited).
            // The `> 0` guard prevents the count going negative; the card was
            // already charged, so we fulfil rather than refuse if it hit zero.
            if (! is_null($product->stock)) {
                Product::whereKey($product->id)->where('stock', '>', 0)->decrement('stock');
            }

            $invoice = $this->invoiceService->createInvoice(
                $dealer,
                "Product purchase: {$product->name}",
                (float) $order->unit_price_net,
                InvoiceType::Product,
                $user,
                $order->id,
                ProductOrder::class,
                (bool) $product->vat_applicable,
            );

            return $this->invoiceService->markPaid($invoice, $paymentIntentId);
        });

        PaymentConfirmed::dispatch($invoice, $dealer);
    }

    private function handleInvoice(object $metadata, ?string $paymentIntentId): void
    {
        if ($paymentIntentId && Invoice::where('stripe_payment_intent_id', $paymentIntentId)->exists()) {
            Log::info('Stripe webhook: duplicate invoice event skipped', ['payment_intent_id' => $paymentIntentId]);

            return;
        }

        $invoice = Invoice::findOrFail($metadata->invoice_id);

        $dealer = $invoice->dealer;

        $this->invoiceService->markPaid($invoice, $paymentIntentId);

        PaymentConfirmed::dispatch($invoice, $dealer);
    }
}
