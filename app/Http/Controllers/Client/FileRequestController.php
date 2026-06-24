<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FileRequestController extends Controller
{
    public function index(Request $request)
    {
        return response("FileRequestController@index placeholder");
    }

    public function archive(Request $request)
    {
        return back();
    }

    public function show(Request $request)
    {
        return response("FileRequestController@show placeholder");
    }

}
