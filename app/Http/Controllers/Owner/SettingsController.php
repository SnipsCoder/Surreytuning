<?php

namespace App\Http\Controllers\Owner;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\UpdateBrandingRequest;
use App\Http\Requests\Owner\UpdateOpeningHoursRequest;
use App\Http\Requests\Owner\UpdateSettingsRequest;
use App\Models\Dealer;
use App\Models\Invoice;
use App\Models\OpeningHour;
use App\Models\Setting;
use App\Services\InvoicePdfService;

class SettingsController extends Controller
{
    public function index()
    {
        return view('owner.settings.index', [
            'settings' => Setting::get(),
            'openingHours' => OpeningHour::orderBy('day_of_week')->get(),
        ]);
    }

    public function update(UpdateSettingsRequest $request)
    {
        $data = $request->validated();
        $data['dealer_auto_onboard'] = $request->boolean('dealer_auto_onboard');

        Setting::get()->update($data);
        Setting::clearCache();

        return back()
            ->with('success', 'Settings updated.')
            ->with('active_tab', $request->input('active_tab', 'account'));
    }

    public function updateHours(UpdateOpeningHoursRequest $request)
    {
        foreach ($request->validated('hours') as $hour) {
            OpeningHour::where('id', $hour['id'])->update([
                'is_open' => $hour['is_open'] ?? false,
                'open_time' => $hour['open_time'],
                'close_time' => $hour['close_time'],
            ]);
        }

        return back()
            ->with('success', 'Opening hours updated.')
            ->with('active_tab', 'hours');
    }

    public function updateBranding(UpdateBrandingRequest $request)
    {
        $settings = Setting::get();
        $data = $request->validated();

        foreach (['logo_light', 'logo_dark', 'login_background', 'portal_logo', 'invoice_header'] as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('branding', 'r2');
            } else {
                unset($data[$field]);
            }
        }

        $settings->update($data);
        Setting::clearCache();

        return back()
            ->with('success', 'Branding updated.')
            ->with('active_tab', 'branding');
    }

    /**
     * Stream a sample invoice through the real PDF pipeline so the owner can
     * preview the layout and see where each loaded setting (brand name, logo,
     * from/bill-to addresses, VAT number, company number, invoice prefix)
     * appears. Nothing is persisted — the Invoice and Dealer are in-memory only.
     */
    public function previewInvoice(InvoicePdfService $pdf)
    {
        $settings = Setting::get();
        $prefix = $settings->invoice_reference_prefix ?: 'INV';

        $dealer = new Dealer([
            'company_name' => 'Sample Motors Ltd',
            'invoice_address' => "12 Example Street\nGuildford\nSurrey\nGU1 1AA",
            'country' => 'United Kingdom',
        ]);

        $net = 100.00;
        $vatRate = (float) $settings->vat_rate;
        $vat = round($net * $vatRate / 100, 2);

        $invoice = new Invoice([
            'invoice_number' => $settings->invoice_start_number ?: 10000,
            'status' => InvoiceStatus::Paid,
            'type' => InvoiceType::CreditTopUp,
            'description' => 'Sample credit top-up (preview only)',
            'amount_net' => $net,
            'vat_amount' => $vat,
            'amount_gross' => $net + $vat,
        ]);
        $invoice->created_at = now();
        $invoice->paid_at = now();

        // make() calls loadMissing('dealer'); pre-set the relation so no query runs.
        $invoice->setRelation('dealer', $dealer);

        return $pdf->make($invoice)->stream('test-invoice.pdf');
    }
}
