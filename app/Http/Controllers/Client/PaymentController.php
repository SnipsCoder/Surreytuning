<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function success(Request $request)
    {
        return view('client.payment.success');
    }

    public function cancel(Request $request)
    {
        return view('client.payment.cancel');
    }
}
