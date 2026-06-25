<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreTuningToolRequest;
use App\Models\TuningTool;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TuningToolController extends Controller
{
    public function index(): View
    {
        $tuningTools = TuningTool::orderBy('sort_order')->orderBy('name')->get();

        return view('owner.tools.index', compact('tuningTools'));
    }

    public function store(StoreTuningToolRequest $request): RedirectResponse
    {
        TuningTool::create($request->validated());

        return back()->with('success', 'Tool created.');
    }

    public function update(StoreTuningToolRequest $request, TuningTool $tool): RedirectResponse
    {
        $tool->update($request->validated());

        return back()->with('success', 'Tool updated.');
    }

    public function destroy(TuningTool $tool): RedirectResponse
    {
        if ($tool->fileRequests()->exists()) {
            return back()->with('error', 'Cannot delete a tool that has file requests.');
        }

        $tool->delete();

        return back()->with('success', 'Tool deleted.');
    }
}
