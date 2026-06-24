<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        return response("ProductController@index placeholder");
    }

    public function purchase(Request $request)
    {
        return back();
    }

}
