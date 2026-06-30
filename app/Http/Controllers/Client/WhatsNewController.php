<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\WhatsNew;

class WhatsNewController extends Controller
{
    public function index()
    {
        $whatsNews = WhatsNew::orderByDesc('published_at')->orderByDesc('created_at')->get();

        return view('client.whats-new.index', compact('whatsNews'));
    }
}
