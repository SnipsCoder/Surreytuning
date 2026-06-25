<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\FileRequestAttachment;
use App\Services\FileStorageService;
use Illuminate\Http\Request;

class FileDownloadController extends Controller
{
    public function download(Request $request, FileRequestAttachment $attachment, FileStorageService $fileStorageService)
    {
        $this->authorize('view', $attachment->fileRequest);

        if (is_null($attachment->first_downloaded_at)) {
            $attachment->update(['first_downloaded_at' => now()]);
        }

        if (is_null($attachment->fileRequest->client_downloaded_at)) {
            $attachment->fileRequest->update(['client_downloaded_at' => now()]);
        }

        return redirect()->away($fileStorageService->getTemporaryUrl($attachment->file_path, 30));
    }
}
