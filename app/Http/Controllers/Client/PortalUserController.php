<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PortalUserController extends Controller
{
    public function index(Request $request)
    {
        return response("PortalUserController@index placeholder");
    }

    public function invite(Request $request)
    {
        return back();
    }

    public function destroy(Request $request)
    {
        return back();
    }

}
