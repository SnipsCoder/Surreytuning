<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VehicleStatController extends Controller
{
    public function index(Request $request)
    {
        return response("VehicleStatController@index placeholder");
    }

}
