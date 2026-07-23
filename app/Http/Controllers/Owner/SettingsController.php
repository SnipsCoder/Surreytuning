<?php

namespace App\Http\Controllers\Owner;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\UpdateBrandingRequest;
use App\Http\Requests\Owner\UpdateFuelTypesRequest;
use App\Http\Requests\Owner\UpdateOpeningHoursRequest;
use App\Http\Requests\Owner\UpdatePaymentKeysRequest;
use App\Http\Requests\Owner\UpdateSettingsRequest;
use App\Models\Dealer;
use App\Models\Invoice;
use App\Models\OpeningHour;
use App\Models\Setting;
use App\Services\InvoicePdfService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * How long a payment-settings unlock (via authenticator code) stays valid.
     */
    private const PAYMENTS_UNLOCK_MINUTES = 15;

    public function index(Request $request)
    {
        return view('owner.settings.index', [
            'settings' => Setting::get(),
            'openingHours' => OpeningHour::orderBy('day_of_week')->get(),
            'paymentsUnlocked' => $this->paymentsUnlocked($request),
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

    /**
     * Step-up authentication: unlock the payment settings by entering a current
     * Google Authenticator (TOTP) code. Unlocks for a short window only.
     */
    public function unlockPayments(Request $request)
    {
        $request->validate(['code' => ['required', 'string']]);

        $user = $request->user();

        if (! $user->hasAuthenticator()) {
            return back()
                ->with('active_tab', 'payments')
                ->with('error', 'Set up Google Authenticator on your account (Profile → Two-factor) before accessing payment settings.');
        }

        $code = preg_replace('/\s+/', '', (string) $request->input('code'));

        if (! $user->verifyTotpCode($code)) {
            return back()
                ->with('active_tab', 'payments')
                ->with('error', 'That authenticator code was not valid. Please try again.');
        }

        $request->session()->put(
            'payments_unlocked_until',
            now()->addMinutes(self::PAYMENTS_UNLOCK_MINUTES)->toIso8601String()
        );

        return back()
            ->with('active_tab', 'payments')
            ->with('success', 'Payment settings unlocked for '.self::PAYMENTS_UNLOCK_MINUTES.' minutes.');
    }

    public function updatePayments(UpdatePaymentKeysRequest $request)
    {
        // Re-check the step-up gate server-side — never trust the UI alone.
        if (! $this->paymentsUnlocked($request)) {
            return redirect()->route('owner.settings.index')
                ->with('active_tab', 'payments')
                ->with('error', 'Your payment-settings session expired. Enter your authenticator code again.');
        }

        $settings = Setting::get();
        $data = $request->validated();

        // Public identifiers: update (or clear) only the ones actually submitted
        // — so editing one provider's card never wipes another's.
        foreach (['stripe_public_key', 'evc_account_number', 'paypal_client_id'] as $field) {
            if ($request->has($field)) {
                $settings->{$field} = $data[$field] ?: null;
            }
        }

        // Secrets: only overwrite when a new value was typed (blank = keep the
        // existing one, which is never sent back to the browser).
        foreach (['stripe_secret_key', 'stripe_webhook_secret', 'evc_password', 'paypal_secret'] as $secret) {
            if ($request->filled($secret)) {
                $settings->{$secret} = $data[$secret];
            }
        }

        $settings->save();
        Setting::clearCache();

        return back()
            ->with('success', 'Payment settings updated.')
            ->with('active_tab', 'payments');
    }

    private function paymentsUnlocked(Request $request): bool
    {
        $until = $request->session()->get('payments_unlocked_until');

        return $until !== null && now()->lessThan(\Illuminate\Support\Carbon::parse($until));
    }

    public function updateFuelTypes(UpdateFuelTypesRequest $request)
    {
        Setting::get()->update(['fuel_types' => $request->validated('fuel_types')]);
        Setting::clearCache();

        return back()
            ->with('success', 'Fuel types updated.')
            ->with('active_tab', 'fuel_types');
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
