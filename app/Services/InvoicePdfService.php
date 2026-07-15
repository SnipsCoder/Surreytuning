<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Invoice;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPDF;

class InvoicePdfService
{
    /**
     * Build a rendered DomPDF instance for the given invoice.
     */
    public function make(Invoice $invoice): DomPDF
    {
        $invoice->loadMissing('dealer');

        return Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice,
            'settings' => Setting::get(),
        ])->setPaper('a4');
    }

    /**
     * Suggested download filename for the invoice PDF, e.g. "INV-10001.pdf".
     */
    public function filename(Invoice $invoice): string
    {
        $prefix = Setting::get()->invoice_reference_prefix ?: 'INV';

        return $prefix.'-'.$invoice->invoice_number.'.pdf';
    }
}
