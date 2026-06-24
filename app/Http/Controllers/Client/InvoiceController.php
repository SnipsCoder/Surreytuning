<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        return response("InvoiceController@index placeholder");
    }

    public function show(Request $request)
    {
        return response("InvoiceController@show placeholder");
    }

    public function pay(Request $request)
    {
        return back();
    }

}
