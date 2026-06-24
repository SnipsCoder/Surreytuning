<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DealerApplicationController extends Controller
{
    public function create(Request $request)
    {
        return response("DealerApplicationController@create placeholder");
    }

    public function store(Request $request)
    {
        return back();
    }

}
