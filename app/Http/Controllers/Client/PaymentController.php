<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Throwable;

class PaymentController extends Controller
{
    public function __construct(private StripeService $stripeService)
    {
    }

    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');
        $verified = false;

        if ($sessionId) {
            try {
                $session = $this->stripeService->retrieveCheckoutSession($sessionId);
                $verified = $session->payment_status === 'paid';
            } catch (Throwable) {
                $verified = false;
            }
        }

        return view('client.payment.success', ['verified' => $verified]);
    }

    public function cancel(Request $request)
    {
        return view('client.payment.cancel');
    }
}
