<?php

namespace App\Http\Controllers\Owner;

use App\Enums\FileRequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreFileStageRequest;
use App\Models\FileStage;
use Illuminate\Http\RedirectResponse;

class FileStageController extends Controller
{
    public function index()
    {
        return view('owner.file-stages.index', [
            'fileStages' => FileStage::orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreFileStageRequest $request): RedirectResponse
    {
        FileStage::create($request->validated());

        return back()->with('success', 'File stage created.');
    }

    public function update(StoreFileStageRequest $request, FileStage $fileStage): RedirectResponse
    {
        $fileStage->update($request->validated());

        return back()->with('success', 'File stage updated.');
    }

    public function destroy(FileStage $fileStage): RedirectResponse
    {
        $activeJobs = $fileStage->fileRequests()
            ->whereNotIn('status', [FileRequestStatus::Closed, FileRequestStatus::Void])
            ->exists();

        if ($activeJobs) {
            return back()->with('error', 'Cannot delete this file stage: it has active file requests.');
        }

        if ($fileStage->fileOptions()->exists()) {
            return back()->with('error', 'Cannot delete this file stage: it has file options attached.');
        }

        $fileStage->delete();

        return back()->with('success', 'File stage deleted.');
    }
}
