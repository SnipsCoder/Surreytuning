<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SlaveCreditController extends Controller
{
    public function index(Request $request)
    {
        return response("SlaveCreditController@index placeholder");
    }

    public function checkout(Request $request)
    {
        return back();
    }

}
