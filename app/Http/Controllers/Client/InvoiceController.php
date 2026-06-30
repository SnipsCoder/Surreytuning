<?php

namespace App\Http\Controllers\Client;

use App\Enums\InvoiceStatus;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\StripeService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(private StripeService $stripeService)
    {
    }

    public function index(Request $request)
    {
        $status = $request->query('status');

        $query = $request->user()->dealer->invoices()->latest();

        if (in_array($status, ['issued', 'paid', 'void'], true)) {
            $query->where('status', $status);
        }

        $invoices = $query->paginate(15)->withQueryString();

        return view('client.invoices.index', [
            'invoices' => $invoices,
            'currentStatus' => $status,
        ]);
    }

    public function show(Request $request, Invoice $invoice)
    {
        abort_unless($invoice->dealer_id === $request->user()->dealer_id, 403);

        return view('client.invoices.show', [
            'invoice' => $invoice,
        ]);
    }

    public function pay(Request $request, Invoice $invoice)
    {
        abort_unless($invoice->dealer_id === $request->user()->dealer_id, 403);

        if ($invoice->status !== InvoiceStatus::Issued) {
            return back()->with('error', 'This invoice cannot be paid.');
        }

        $session = $this->stripeService->createCheckoutSession(
            [[
                'price_data' => [
                    'currency' => 'gbp',
                    'product_data' => ['name' => "Invoice #{$invoice->invoice_number}"],
                    'unit_amount' => (int) round($invoice->amount_gross * 100),
                ],
                'quantity' => 1,
            ]],
            route('client.payment.success') . '?session_id={CHECKOUT_SESSION_ID}',
            route('client.payment.cancel'),
            [
                'type' => 'invoice',
                'invoice_id' => $invoice->id,
            ]
        );

        return redirect($session->url);
    }
}
