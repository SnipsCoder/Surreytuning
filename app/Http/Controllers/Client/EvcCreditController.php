<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\WinolsBundle;
use App\Services\StripeService;
use Illuminate\Http\Request;

class EvcCreditController extends Controller
{
    public function __construct(private StripeService $stripeService) {}

    public function index(Request $request)
    {
        $dealer = $request->user()->dealer;

        $bundles = WinolsBundle::where('is_active', true)->orderBy('price_net')->get();

        $transactions = $dealer->evcCreditTransactions()
            ->latest()
            ->paginate(15);

        return view('client.credits.evc', [
            'dealer' => $dealer,
            'bundles' => $bundles,
            'transactions' => $transactions,
        ]);
    }

    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'winols_bundle_id' => ['required', 'exists:winols_bundles,id'],
        ]);

        $dealer = $request->user()->dealer;

        // EVC credits can only be purchased once the dealer has linked their EVC
        // number — otherwise there is no account to allocate the credits to.
        if (blank($dealer->evc_number)) {
            return back()->with('error', 'Please add your EVC WinOLS number in Settings before purchasing EVC credits.');
        }

        $bundle = WinolsBundle::where('is_active', true)->findOrFail($validated['winols_bundle_id']);

        $session = $this->stripeService->createCheckoutSession(
            [[
                'price_data' => [
                    'currency' => 'gbp',
                    'product_data' => ['name' => $bundle->name],
                    'unit_amount' => (int) round($dealer->discountedPrice((float) $bundle->price_net) * 100),
                ],
                'quantity' => 1,
            ]],
            route('client.payment.success').'?session_id={CHECKOUT_SESSION_ID}',
            route('client.payment.cancel'),
            [
                'type' => 'evc_bundle',
                'dealer_id' => $dealer->id,
                'user_id' => $request->user()->id,
                'winols_bundle_id' => $bundle->id,
            ]
        );

        return redirect($session->url);
    }
}
