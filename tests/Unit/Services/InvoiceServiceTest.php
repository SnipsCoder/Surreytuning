<?php

namespace Tests\Unit\Services;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Models\Dealer;
use App\Models\Setting;
use App\Services\InvoiceService;
use Tests\TestCase;

class InvoiceServiceTest extends TestCase
{
    public function test_create_invoice_calculates_vat_correctly(): void
    {
        Setting::get()->update(['vat_rate' => 20, 'invoice_start_number' => 10000]);

        $dealer = Dealer::create(['company_name' => 'Test Dealer', 'country' => 'UK']);

        $invoice = (new InvoiceService)->createInvoice($dealer, 'Test charge', 100, InvoiceType::Manual);

        $this->assertEquals(100, $invoice->amount_net);
        $this->assertEquals(20, $invoice->vat_amount);
        $this->assertEquals(120, $invoice->amount_gross);
    }

    public function test_create_invoice_assigns_correct_sequential_invoice_number(): void
    {
        Setting::get()->update(['vat_rate' => 20, 'invoice_start_number' => 10000]);

        $dealer = Dealer::create(['company_name' => 'Test Dealer', 'country' => 'UK']);

        $first = (new InvoiceService)->createInvoice($dealer, 'First', 100, InvoiceType::Manual);

        $this->assertEquals(10000, $first->invoice_number);

        $second = (new InvoiceService)->createInvoice($dealer, 'Second', 100, InvoiceType::Manual);

        $this->assertGreaterThan($first->invoice_number, $second->invoice_number);
    }

    public function test_create_invoice_can_skip_vat(): void
    {
        Setting::get()->update(['vat_rate' => 20, 'invoice_start_number' => 10000]);

        $dealer = Dealer::create(['company_name' => 'Test Dealer', 'country' => 'UK']);

        $invoice = (new InvoiceService)->createInvoice($dealer, 'No VAT charge', 100, InvoiceType::Manual, applyVat: false);

        $this->assertEquals(100, $invoice->amount_net);
        $this->assertEquals(0, $invoice->vat_amount);
        $this->assertEquals(100, $invoice->amount_gross);
    }

    public function test_mark_paid_sets_status_and_payment_intent(): void
    {
        Setting::get()->update(['vat_rate' => 20, 'invoice_start_number' => 10000]);

        $dealer = Dealer::create(['company_name' => 'Test Dealer', 'country' => 'UK']);
        $service = new InvoiceService;

        $invoice = $service->createInvoice($dealer, 'Test charge', 100, InvoiceType::Manual);
        $this->assertEquals(InvoiceStatus::Issued, $invoice->status);

        $service->markPaid($invoice, 'pi_test_123');
        $invoice->refresh();

        $this->assertEquals(InvoiceStatus::Paid, $invoice->status);
        $this->assertNotNull($invoice->paid_at);
        $this->assertEquals('pi_test_123', $invoice->stripe_payment_intent_id);
    }

    public function test_mark_paid_preserves_existing_payment_intent_when_not_supplied(): void
    {
        Setting::get()->update(['vat_rate' => 20, 'invoice_start_number' => 10000]);

        $dealer = Dealer::create(['company_name' => 'Test Dealer', 'country' => 'UK']);
        $service = new InvoiceService;

        $invoice = $service->createInvoice($dealer, 'Test charge', 100, InvoiceType::Manual);
        $invoice->update(['stripe_payment_intent_id' => 'pi_existing']);

        $service->markPaid($invoice);
        $invoice->refresh();

        $this->assertEquals('pi_existing', $invoice->stripe_payment_intent_id);
    }

    public function test_void_invoice_sets_void_status(): void
    {
        Setting::get()->update(['vat_rate' => 20, 'invoice_start_number' => 10000]);

        $dealer = Dealer::create(['company_name' => 'Test Dealer', 'country' => 'UK']);
        $service = new InvoiceService;

        $invoice = $service->createInvoice($dealer, 'Test charge', 100, InvoiceType::Manual);
        $service->voidInvoice($invoice);
        $invoice->refresh();

        $this->assertEquals(InvoiceStatus::Void, $invoice->status);
    }
}
