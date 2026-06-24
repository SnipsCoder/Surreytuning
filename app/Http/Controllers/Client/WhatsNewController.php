<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WhatsNewController extends Controller
{
    public function index(Request $request)
    {
        return response("WhatsNewController@index placeholder");
    }

}
