<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreWhatsNewRequest;
use App\Models\WhatsNew;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WhatsNewController extends Controller
{
    public function index(): View
    {
        $whatsNews = WhatsNew::orderByDesc('published_at')->orderByDesc('created_at')->get();

        return view('owner.whats-new.index', compact('whatsNews'));
    }

    public function store(StoreWhatsNewRequest $request): RedirectResponse
    {
        WhatsNew::create($request->validated());

        return back()->with('success', 'Update posted.');
    }

    public function update(StoreWhatsNewRequest $request, WhatsNew $whatsNew): RedirectResponse
    {
        $whatsNew->update($request->validated());

        return back()->with('success', 'Update saved.');
    }

    public function destroy(WhatsNew $whatsNew): RedirectResponse
    {
        $whatsNew->delete();

        return back()->with('success', 'Update deleted.');
    }
}
