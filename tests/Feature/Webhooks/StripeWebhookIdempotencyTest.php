<?php

namespace Tests\Feature\Webhooks;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Events\PaymentConfirmed;
use App\Models\Dealer;
use App\Models\ProcessedStripeEvent;
use App\Services\InvoiceService;
use App\Services\StripeService;
use Illuminate\Support\Facades\Event;
use Stripe\Event as StripeEvent;
use Stripe\Exception\SignatureVerificationException;
use Tests\TestCase;

class StripeWebhookIdempotencyTest extends TestCase
{
    private function fakeEvent(string $eventId, int $invoiceId): StripeEvent
    {
        return StripeEvent::constructFrom([
            'id' => $eventId,
            'type' => 'checkout.session.completed',
            'created' => time(),
            'data' => [
                'object' => [
                    'payment_intent' => 'pi_test_'.$eventId,
                    'metadata' => [
                        'type' => 'invoice',
                        'invoice_id' => $invoiceId,
                    ],
                ],
            ],
        ]);
    }

    /**
     * Bind a StripeService whose signature verification is stubbed to return
     * the given event, so the controller runs without a real Stripe signature.
     */
    private function stubStripe(StripeEvent $event): void
    {
        $this->mock(StripeService::class, function ($mock) use ($event) {
            $mock->shouldReceive('constructWebhookEvent')->andReturn($event);
        });
    }

    public function test_a_new_event_is_processed_and_recorded(): void
    {
        $dealer = Dealer::factory()->create();
        $invoice = (new InvoiceService)->createInvoice($dealer, 'Test invoice', 100, InvoiceType::Manual);

        $this->stubStripe($this->fakeEvent('evt_new_1', $invoice->id));

        $this->postJson('/webhooks/stripe', [], ['Stripe-Signature' => 't=1,v1=stub'])
            ->assertOk();

        $invoice->refresh();
        $this->assertEquals(InvoiceStatus::Paid, $invoice->status);
        $this->assertSame(1, ProcessedStripeEvent::where('event_id', 'evt_new_1')->count());
    }

    public function test_a_redelivered_event_is_a_no_op(): void
    {
        Event::fake([PaymentConfirmed::class]);

        $dealer = Dealer::factory()->create();
        $invoice = (new InvoiceService)->createInvoice($dealer, 'Test invoice', 100, InvoiceType::Manual);

        $event = $this->fakeEvent('evt_dupe_1', $invoice->id);
        $this->stubStripe($event);

        // First delivery: processed, PaymentConfirmed dispatched once.
        $this->postJson('/webhooks/stripe', [], ['Stripe-Signature' => 't=1,v1=stub'])->assertOk();

        // Second delivery of the SAME event id: short-circuited before any handler.
        $this->postJson('/webhooks/stripe', [], ['Stripe-Signature' => 't=1,v1=stub'])
            ->assertOk()
            ->assertSee('Webhook already handled');

        // Side effects happened exactly once.
        Event::assertDispatchedTimes(PaymentConfirmed::class, 1);
        $this->assertSame(1, ProcessedStripeEvent::where('event_id', 'evt_dupe_1')->count());
    }

    public function test_an_invalid_signature_is_rejected(): void
    {
        $this->mock(StripeService::class, function ($mock) {
            $mock->shouldReceive('constructWebhookEvent')
                ->andThrow(new SignatureVerificationException('bad signature'));
        });

        $this->postJson('/webhooks/stripe', [], ['Stripe-Signature' => 'bad'])
            ->assertStatus(400);

        $this->assertSame(0, ProcessedStripeEvent::count());
    }
}
