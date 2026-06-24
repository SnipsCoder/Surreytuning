<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DtcSearchController extends Controller
{
    public function index(Request $request)
    {
        return response("DtcSearchController@index placeholder");
    }

    public function search(Request $request)
    {
        return response("DtcSearchController@search placeholder");
    }

}
