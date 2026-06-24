<?php

namespace App\Http\Controllers\Owner;

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

    public function store(Request $request)
    {
        return back();
    }

    public function void(Request $request)
    {
        return back();
    }

    public function markPaid(Request $request)
    {
        return back();
    }

}
