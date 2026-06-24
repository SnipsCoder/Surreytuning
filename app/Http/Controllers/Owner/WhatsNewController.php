<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WhatsNewController extends Controller
{
    public function index(Request $request)
    {
        return response("WhatsNewController@index placeholder");
    }

    public function create(Request $request)
    {
        return response("WhatsNewController@create placeholder");
    }

    public function store(Request $request)
    {
        return back();
    }

    public function edit(Request $request)
    {
        return response("WhatsNewController@edit placeholder");
    }

    public function update(Request $request)
    {
        return back();
    }

    public function destroy(Request $request)
    {
        return back();
    }

}
