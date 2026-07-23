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
        // Unchecked checkboxes aren't sent — resolve them explicitly.
        $data['is_active'] = $request->boolean('is_active');
        $data['show_on_front_page'] = $request->boolean('show_on_front_page');

        Noticeboard::create($data);

        return back()->with('success', 'Notice created.');
    }

    public function update(StoreNoticeboardRequest $request, Noticeboard $noticeboard): RedirectResponse
    {
        $data = $request->validated();
        // Unchecked checkboxes aren't sent — resolve them explicitly so toggling
        // Active / Display on Front Page off actually persists.
        $data['is_active'] = $request->boolean('is_active');
        $data['show_on_front_page'] = $request->boolean('show_on_front_page');

        $noticeboard->update($data);

        return back()->with('success', 'Notice updated.');
    }

    public function destroy(Noticeboard $noticeboard): RedirectResponse
    {
        $noticeboard->delete();

        return back()->with('success', 'Notice deleted.');
    }
}
