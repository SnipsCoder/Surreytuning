<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FileStageController extends Controller
{
    public function index(Request $request)
    {
        return response("FileStageController@index placeholder");
    }

    public function create(Request $request)
    {
        return response("FileStageController@create placeholder");
    }

    public function store(Request $request)
    {
        return back();
    }

    public function edit(Request $request)
    {
        return response("FileStageController@edit placeholder");
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
