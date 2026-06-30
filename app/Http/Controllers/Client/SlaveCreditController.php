<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SlaveCreditController extends Controller
{
    public function __construct(private StripeService $stripeService)
    {
    }

    public function index(Request $request)
    {
        $dealer = $request->user()->dealer;

        $transactions = $dealer->slaveCreditTransactions()
            ->latest()
            ->paginate(15);

        return view('client.credits.slave', [
            'dealer' => $dealer,
            'transactions' => $transactions,
        ]);
    }

    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:10', 'max:5000'],
        ]);

        $dealer = $request->user()->dealer;
        $amount = (float) $validated['amount'];

        $session = $this->stripeService->createCheckoutSession(
            [[
                'price_data' => [
                    'currency' => 'gbp',
                    'product_data' => ['name' => 'Slave Credit Top-Up'],
                    'unit_amount' => (int) round($amount * 100),
                ],
                'quantity' => 1,
            ]],
            route('client.payment.success'),
            route('client.payment.cancel'),
            [
                'type' => 'slave_credits',
                'dealer_id' => $dealer->id,
                'user_id' => $request->user()->id,
                'amount' => $amount,
            ]
        );

        return redirect($session->url);
    }
}
