<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreNoticeboardRequest;
use App\Models\Noticeboard;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NoticeboardController extends Controller
{
    public function index(): View
    {
        $noticeboards = Noticeboard::orderByDesc('created_at')->get();

        return view('owner.noticeboards.index', compact('noticeboards'));
    }

    public function store(StoreNoticeboardRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by_user_id'] = auth()->id();

        Noticeboard::create($data);

        return back()->with('success', 'Notice created.');
    }

    public function update(StoreNoticeboardRequest $request, Noticeboard $noticeboard): RedirectResponse
    {
        $noticeboard->update($request->validated());

        return back()->with('success', 'Notice updated.');
    }

    public function destroy(Noticeboard $noticeboard): RedirectResponse
    {
        $noticeboard->delete();

        return back()->with('success', 'Notice deleted.');
    }
}
