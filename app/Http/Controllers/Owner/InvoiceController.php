<?php

namespace App\Http\Controllers\Owner;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Http\Controllers\Controller;
use App\Mail\InvoiceMail;
use App\Models\Dealer;
use App\Models\Invoice;
use App\Services\InvoicePdfService;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class InvoiceController extends Controller
{
    public function __construct(private InvoiceService $invoiceService) {}

    public function index(Request $request)
    {
        $query = Invoice::with('dealer')->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('dealer_id')) {
            $query->where('dealer_id', $request->dealer_id);
        }

        $invoices = $query->paginate(25)->withQueryString();
        $dealers = Dealer::orderBy('company_name')->get(['id', 'company_name']);

        return view('owner.invoices.index', [
            'invoices' => $invoices,
            'dealers' => $dealers,
            'statuses' => InvoiceStatus::cases(),
        ]);
    }

    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $invoice->load('dealer', 'user');

        return view('owner.invoices.show', compact('invoice'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'dealer_id' => ['required', 'exists:dealers,id'],
            'description' => ['required', 'string', 'max:500'],
            'amount_net' => ['required', 'numeric', 'min:0.01'],
            'apply_vat' => ['sometimes', 'boolean'],
        ]);

        $dealer = Dealer::findOrFail($data['dealer_id']);
        $applyVat = (bool) ($data['apply_vat'] ?? false);

        $invoice = $this->invoiceService->createInvoice(
            $dealer,
            $data['description'],
            (float) $data['amount_net'],
            InvoiceType::Manual,
            $request->user(),
            null,
            null,
            $applyVat,
        );

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice created successfully.');
    }

    public function void(Request $request, Invoice $invoice)
    {
        $this->authorize('void', $invoice);

        if ($invoice->status === InvoiceStatus::Void) {
            return back()->with('error', 'Invoice is already void.');
        }

        $this->invoiceService->voidInvoice($invoice);

        return back()->with('success', 'Invoice voided.');
    }

    public function markPaid(Request $request, Invoice $invoice)
    {
        $this->authorize('markPaid', $invoice);

        if ($invoice->status === InvoiceStatus::Paid) {
            return back()->with('error', 'Invoice is already paid.');
        }

        $this->invoiceService->markPaid($invoice);

        return back()->with('success', 'Invoice marked as paid.');
    }

    public function download(Invoice $invoice, InvoicePdfService $pdfService)
    {
        $this->authorize('view', $invoice);

        return $pdfService->make($invoice)->download($pdfService->filename($invoice));
    }

    public function send(Request $request, Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $invoice->loadMissing('dealer.primaryContact');

        $recipient = $invoice->dealer->primaryContact?->email
            ?? $invoice->dealer->users()->value('email');

        if (! $recipient) {
            return back()->with('error', 'No contact email is on file for this dealer.');
        }

        $mailer = Mail::to($recipient);

        if ($bcc = \App\Models\Setting::get()->bcc_invoice_email) {
            $mailer->bcc($bcc);
        }

        $mailer->send(new InvoiceMail($invoice));

        return back()->with('success', "Invoice emailed to {$recipient}.");
    }
}
