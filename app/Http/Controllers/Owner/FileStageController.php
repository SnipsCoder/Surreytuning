<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreFileStageRequest;
use App\Models\FileStage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

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
        $data = $this->prepareData($request);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('file-stages', 'public');
        }

        FileStage::create($data);

        return back()->with('success', 'File stage created.');
    }

    public function update(StoreFileStageRequest $request, FileStage $fileStage): RedirectResponse
    {
        $data = $this->prepareData($request);

        if ($request->hasFile('image')) {
            if ($fileStage->image_path) {
                Storage::disk('public')->delete($fileStage->image_path);
            }
            $data['image_path'] = $request->file('image')->store('file-stages', 'public');
        }

        $fileStage->update($data);

        return back()->with('success', 'File stage updated.');
    }

    public function destroy(FileStage $fileStage): RedirectResponse
    {
        if ($fileStage->fileRequests()->exists()) {
            return back()->with('error', 'Cannot delete this stage — it is referenced by existing file requests.');
        }

        if ($fileStage->fileOptions()->exists()) {
            return back()->with('error', 'Cannot delete this file stage: it has file options attached.');
        }

        if ($fileStage->image_path) {
            Storage::disk('public')->delete($fileStage->image_path);
        }

        $fileStage->delete();

        return back()->with('success', 'File stage deleted.');
    }

    /**
     * Normalise validated input: strip the raw upload and coerce checkboxes,
     * which submit nothing when unticked (so validated() omits them). Image
     * storage is handled by the caller.
     */
    private function prepareData(StoreFileStageRequest $request): array
    {
        $data = $request->validated();
        unset($data['image']);
        $data['vat_applicable'] = $request->boolean('vat_applicable');
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
