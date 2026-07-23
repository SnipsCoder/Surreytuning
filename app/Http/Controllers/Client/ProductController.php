<?php

namespace App\Http\Controllers\Client;

use App\Enums\InvoiceType;
use App\Enums\ProductPaymentType;
use App\Exceptions\InsufficientCreditsException;
use App\Exceptions\OutOfStockException;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\Setting;
use App\Services\CreditService;
use App\Services\InvoiceService;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function __construct(
        private CreditService $creditService,
        private InvoiceService $invoiceService,
        private StripeService $stripeService,
    ) {}

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

        if (! is_null($product->stock) && $product->stock <= 0) {
            return back()->with('error', 'This product is out of stock.');
        }

        $paymentMethod = $request->validate([
            'payment_method' => [
                'required',
                Rule::in(['file_credits', 'stripe']),
            ],
        ])['payment_method'];

        if ($product->payment_type === ProductPaymentType::FileCredits && $paymentMethod !== 'file_credits') {
            abort(422, 'This product can only be purchased with file credits.');
        }

        if ($product->payment_type === ProductPaymentType::DirectPayment && $paymentMethod !== 'stripe') {
            abort(422, 'This product can only be purchased by direct payment.');
        }

        $dealer = $request->user()->dealer;
        $unitNet = $dealer->discountedPrice((float) $product->price_net);
        $vatAmount = $product->vat_applicable
            ? round($unitNet * (Setting::get()->vat_rate / 100), 2)
            : 0.00;
        $totalGross = $unitNet + $vatAmount;

        if ($paymentMethod === 'file_credits') {
            try {
                DB::transaction(function () use ($product, $dealer, $request, $unitNet, $vatAmount, $totalGross) {
                    // Atomically claim one unit of stock (NULL = unlimited). If
                    // nothing was decremented the item is sold out, so we abort
                    // the whole purchase — the rollback ensures no credits are
                    // taken. This also closes the oversell race between the
                    // earlier stock check and this write.
                    if (! is_null($product->stock)) {
                        $claimed = Product::whereKey($product->id)->where('stock', '>', 0)->decrement('stock');

                        if ($claimed === 0) {
                            throw new OutOfStockException;
                        }
                    }

                    $this->creditService->deductFileCredits(
                        $dealer,
                        (float) $totalGross,
                        "Purchase of product: {$product->name}",
                        $request->user(),
                    );

                    $order = ProductOrder::create([
                        'dealer_id' => $dealer->id,
                        'user_id' => $request->user()->id,
                        'product_id' => $product->id,
                        'quantity' => 1,
                        'unit_price_net' => $unitNet,
                        'vat_amount' => $vatAmount,
                        'total_gross' => $totalGross,
                        'payment_method' => 'file_credits',
                        'status' => 'paid',
                    ]);

                    $invoice = $this->invoiceService->createInvoice(
                        $dealer,
                        "Product purchase: {$product->name}",
                        (float) $unitNet,
                        InvoiceType::Product,
                        $request->user(),
                        $order->id,
                        ProductOrder::class,
                        (bool) $product->vat_applicable,
                    );
                    $this->invoiceService->markPaid($invoice);
                });
            } catch (InsufficientCreditsException $e) {
                return back()->with('error', 'Insufficient file credit balance for this purchase.');
            } catch (OutOfStockException $e) {
                return back()->with('error', 'This product is out of stock.');
            }

            return redirect()->route('client.products.index')->with('success', 'Product purchased using file credits.');
        }

        $order = ProductOrder::create([
            'dealer_id' => $dealer->id,
            'user_id' => $request->user()->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price_net' => $unitNet,
            'vat_amount' => $vatAmount,
            'total_gross' => $totalGross,
            'payment_method' => 'stripe',
            'status' => 'pending',
        ]);

        $session = $this->stripeService->createCheckoutSession(
            [[
                'price_data' => [
                    'currency' => 'gbp',
                    'product_data' => ['name' => $product->name],
                    'unit_amount' => (int) round($totalGross * 100),
                ],
                'quantity' => 1,
            ]],
            route('client.payment.success').'?session_id={CHECKOUT_SESSION_ID}',
            route('client.payment.cancel'),
            [
                'type' => 'product',
                'dealer_id' => $dealer->id,
                'user_id' => $request->user()->id,
                'product_order_id' => $order->id,
            ]
        );

        return redirect($session->url);
    }
}
