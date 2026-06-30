<?php

namespace App\Http\Controllers\Client;

use App\Enums\ProductPaymentType;
use App\Exceptions\InsufficientCreditsException;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\Setting;
use App\Services\CreditService;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function __construct(
        private CreditService $creditService,
        private StripeService $stripeService,
    ) {
    }

    public function index(Request $request)
    {
        $products = Product::where('is_active', true)->orderBy('sort_order')->get();

        return view('client.products.index', [
            'products' => $products,
            'dealer' => $request->user()->dealer,
        ]);
    }

    public function purchase(Request $request, Product $product)
    {
        if (! $product->is_active) {
            abort(404);
        }

        $paymentMethod = $request->validate([
            'payment_method' => [
                'required',
                Rule::in(['slave_credits', 'stripe']),
            ],
        ])['payment_method'];

        if ($product->payment_type === ProductPaymentType::SlaveCredits && $paymentMethod !== 'slave_credits') {
            abort(422, 'This product can only be purchased with slave credits.');
        }

        if ($product->payment_type === ProductPaymentType::DirectPayment && $paymentMethod !== 'stripe') {
            abort(422, 'This product can only be purchased by direct payment.');
        }

        $dealer = $request->user()->dealer;
        $vatAmount = $product->vat_applicable
            ? round($product->price_net * (Setting::get()->vat_rate / 100), 2)
            : 0.00;
        $totalGross = $product->price_net + $vatAmount;

        if ($paymentMethod === 'slave_credits') {
            try {
                $this->creditService->deductSlaveCredits(
                    $dealer,
                    (float) $totalGross,
                    "Purchase of product: {$product->name}",
                    $request->user(),
                );
            } catch (InsufficientCreditsException $e) {
                return back()->with('error', 'Insufficient slave credit balance for this purchase.');
            }

            ProductOrder::create([
                'dealer_id' => $dealer->id,
                'user_id' => $request->user()->id,
                'product_id' => $product->id,
                'quantity' => 1,
                'unit_price_net' => $product->price_net,
                'vat_amount' => $vatAmount,
                'total_gross' => $totalGross,
                'payment_method' => 'slave_credits',
                'status' => 'paid',
            ]);

            return redirect()->route('client.products.index')->with('success', 'Product purchased using slave credits.');
        }

        $session = $this->stripeService->createCheckoutSession(
            [[
                'price_data' => [
                    'currency' => 'gbp',
                    'product_data' => ['name' => $product->name],
                    'unit_amount' => (int) round($totalGross * 100),
                ],
                'quantity' => 1,
            ]],
            route('client.payment.success'),
            route('client.payment.cancel'),
            [
                'type' => 'product',
                'dealer_id' => $dealer->id,
                'user_id' => $request->user()->id,
                'product_id' => $product->id,
                'unit_price_net' => $product->price_net,
                'vat_amount' => $vatAmount,
                'total_gross' => $totalGross,
            ]
        );

        return redirect($session->url);
    }
}
