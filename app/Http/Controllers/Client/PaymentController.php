<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function success(Request $request)
    {
        return response("PaymentController@success placeholder");
    }

    public function cancel(Request $request)
    {
        return response("PaymentController@cancel placeholder");
    }

}
