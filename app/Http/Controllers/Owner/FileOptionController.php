<?php

namespace App\Http\Controllers\Owner;

use App\Enums\FileRequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreFileOptionRequest;
use App\Models\FileOption;
use App\Models\FileStage;
use Illuminate\Http\RedirectResponse;

class FileOptionController extends Controller
{
    public function index()
    {
        return view('owner.file-options.index', [
            'fileOptions' => FileOption::with('fileStage')->orderBy('sort_order')->orderBy('name')->get(),
            'fileStages' => FileStage::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreFileOptionRequest $request): RedirectResponse
    {
        FileOption::create($request->validated());

        return back()->with('success', 'File option created.');
    }

    public function update(StoreFileOptionRequest $request, FileOption $fileOption): RedirectResponse
    {
        $fileOption->update($request->validated());

        return back()->with('success', 'File option updated.');
    }

    public function destroy(FileOption $fileOption): RedirectResponse
    {
        $activeJobs = $fileOption->fileRequests()
            ->whereNotIn('status', [FileRequestStatus::Closed, FileRequestStatus::Void])
            ->exists();

        if ($activeJobs) {
            return back()->with('error', 'Cannot delete this file option: it has active file requests.');
        }

        $fileOption->delete();

        return back()->with('success', 'File option deleted.');
    }
}
