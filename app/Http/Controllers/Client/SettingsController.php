<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        return response("SettingsController@index placeholder");
    }

    public function update(Request $request)
    {
        return back();
    }

}
