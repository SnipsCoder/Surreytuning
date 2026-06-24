<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FileUploadController extends Controller
{
    public function create(Request $request)
    {
        return response("FileUploadController@create placeholder");
    }

    public function store(Request $request)
    {
        return back();
    }

}
