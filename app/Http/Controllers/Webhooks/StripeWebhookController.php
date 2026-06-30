<?php

namespace App\Http\Controllers\Webhooks;

use App\Enums\InvoiceType;
use App\Http\Controllers\Controller;
use App\Models\Dealer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\User;
use App\Models\WinolsBundle;
use App\Services\CreditService;
use App\Services\InvoiceService;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use UnexpectedValueException;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller
{
    public function __construct(
        private StripeService $stripeService,
        private CreditService $creditService,
        private InvoiceService $invoiceService,
    ) {
    }

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

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $metadata = $session->metadata;
            $paymentIntentId = $session->payment_intent;

            match ($metadata->type ?? null) {
                'slave_credits' => $this->handleSlaveCredits($metadata, $paymentIntentId),
                'evc_bundle' => $this->handleEvcBundle($metadata, $paymentIntentId),
                'product' => $this->handleProduct($metadata, $paymentIntentId),
                'invoice' => $this->handleInvoice($metadata, $paymentIntentId),
                default => Log::warning('Stripe webhook: unrecognised metadata type', ['metadata' => (array) $metadata]),
            };
        }

        return response('Webhook handled', 200);
    }

    private function handleSlaveCredits(object $metadata, ?string $paymentIntentId): void
    {
        $dealer = Dealer::findOrFail($metadata->dealer_id);
        $user = User::findOrFail($metadata->user_id);
        $amount = (float) $metadata->amount;

        $this->creditService->addSlaveCredits(
            $dealer,
            $amount,
            'Stripe slave credit top-up',
            $user,
        );

        $invoice = $this->invoiceService->createInvoice(
            $dealer,
            'Slave credit top-up',
            $amount,
            InvoiceType::CreditTopUp,
            $user,
        );

        $this->invoiceService->markPaid($invoice, $paymentIntentId);
    }

    private function handleEvcBundle(object $metadata, ?string $paymentIntentId): void
    {
        $dealer = Dealer::findOrFail($metadata->dealer_id);
        $user = User::findOrFail($metadata->user_id);
        $bundle = WinolsBundle::findOrFail($metadata->winols_bundle_id);

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
            (float) $bundle->price_net,
            InvoiceType::EvcBundle,
            $user,
            $bundle->id,
            WinolsBundle::class,
        );

        $this->invoiceService->markPaid($invoice, $paymentIntentId);
    }

    private function handleProduct(object $metadata, ?string $paymentIntentId): void
    {
        $dealer = Dealer::findOrFail($metadata->dealer_id);
        $user = User::findOrFail($metadata->user_id);
        $product = Product::findOrFail($metadata->product_id);

        $order = ProductOrder::create([
            'dealer_id' => $dealer->id,
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price_net' => (float) $metadata->unit_price_net,
            'vat_amount' => (float) $metadata->vat_amount,
            'total_gross' => (float) $metadata->total_gross,
            'payment_method' => 'stripe',
            'stripe_payment_intent_id' => $paymentIntentId,
            'status' => 'paid',
        ]);

        $invoice = $this->invoiceService->createInvoice(
            $dealer,
            "Product purchase: {$product->name}",
            (float) $metadata->unit_price_net,
            InvoiceType::Product,
            $user,
            $order->id,
            ProductOrder::class,
        );

        $this->invoiceService->markPaid($invoice, $paymentIntentId);
    }

    private function handleInvoice(object $metadata, ?string $paymentIntentId): void
    {
        $invoice = Invoice::findOrFail($metadata->invoice_id);

        $this->invoiceService->markPaid($invoice, $paymentIntentId);
    }
}
