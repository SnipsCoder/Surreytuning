<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TuningToolController extends Controller
{
    public function index(Request $request)
    {
        return response("TuningToolController@index placeholder");
    }

    public function create(Request $request)
    {
        return response("TuningToolController@create placeholder");
    }

    public function store(Request $request)
    {
        return back();
    }

    public function edit(Request $request)
    {
        return response("TuningToolController@edit placeholder");
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
