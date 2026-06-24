<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EvcCreditController extends Controller
{
    public function index(Request $request)
    {
        return response("EvcCreditController@index placeholder");
    }

    public function checkout(Request $request)
    {
        return back();
    }

}
