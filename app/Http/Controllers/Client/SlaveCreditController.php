<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Setting;
use App\Services\StripeService;
use Illuminate\Http\Request;

class SlaveCreditController extends Controller
{
    public function __construct(private StripeService $stripeService) {}

    public function index(Request $request)
    {
        $dealer = $request->user()->dealer;

        $products = Product::where('is_active', true)
            ->whereIn('payment_type', ['slave_credits', 'both'])
            ->orderBy('sort_order')
            ->get();

        $transactions = $dealer->slaveCreditTransactions()
            ->latest()
            ->paginate(15);

        return view('client.credits.slave', [
            'dealer' => $dealer,
            'products' => $products,
            'transactions' => $transactions,
        ]);
    }

    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
        ]);

        $product = Product::where('is_active', true)
            ->whereIn('payment_type', ['slave_credits', 'both'])
            ->findOrFail($validated['product_id']);

        $dealer = $request->user()->dealer;
        $settings = Setting::get();
        $amountNet = (float) $product->price_net;
        $amountGross = round($amountNet * (1 + $settings->vat_rate / 100), 2);

        $session = $this->stripeService->createCheckoutSession(
            [[
                'price_data' => [
                    'currency' => 'gbp',
                    'product_data' => ['name' => $product->name],
                    'unit_amount' => (int) round($amountGross * 100),
                ],
                'quantity' => 1,
            ]],
            route('client.payment.success').'?session_id={CHECKOUT_SESSION_ID}',
            route('client.payment.cancel'),
            [
                'type' => 'slave_credits',
                'dealer_id' => $dealer->id,
                'user_id' => $request->user()->id,
                'product_id' => $product->id,
            ]
        );

        return redirect($session->url);
    }
}
