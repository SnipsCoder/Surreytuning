<?php

namespace Tests\Unit\Services;

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
}
