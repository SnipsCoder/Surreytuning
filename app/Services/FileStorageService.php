<?php

namespace App\Services;

use App\Enums\AttachmentType;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class FileStorageService
{
    private function disk(): Filesystem
    {
        return Storage::disk(config('filesystems.file_storage_disk', 'r2'));
    }

    public function storeFile(UploadedFile $file, string $dealerId, string $requestNumber, AttachmentType $type): array
    {
        $this->validateFile($file);

        $originalFilename = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $sanitisedFilename = Str::slug(pathinfo($originalFilename, PATHINFO_FILENAME)).'-'.Str::random(8).'.'.$extension;

        $path = "files/{$dealerId}/{$requestNumber}/{$type->value}/{$sanitisedFilename}";

        $this->disk()->put($path, file_get_contents($file->getRealPath()));

        return [
            'path' => $path,
            'stored_filename' => $sanitisedFilename,
            'original_filename' => $originalFilename,
            'file_size_bytes' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ];
    }

    public function getTemporaryUrl(string $path, int $minutes = 30): string
    {
        $disk = $this->disk();

        if (! $disk->providesTemporaryUrls()) {
            // The configured disk cannot sign short-lived URLs. Falling back to a
            // permanent, unsigned URL would expose a customer's ECU binary to
            // anyone with the link — a data leak. In production we refuse rather
            // than serve a public link; locally (e.g. the 'local'/'public' disk
            // used in dev and tests) we log and fall back so downloads still work.
            Log::critical('File storage disk cannot produce temporary URLs.', [
                'disk' => config('filesystems.file_storage_disk', 'r2'),
                'path' => $path,
            ]);

            if (app()->environment('production')) {
                throw new RuntimeException('Secure file downloads are temporarily unavailable. Please try again later.');
            }

            return $disk->url($path);
        }

        return $disk->temporaryUrl($path, now()->addMinutes($minutes));
    }

    public function deleteFile(string $path): bool
    {
        return $this->disk()->delete($path);
    }

    public function getAllowedMimeTypes(): array
    {
        return ['application/octet-stream', 'application/x-binary', 'text/plain'];
    }

    public function getAllowedExtensions(): array
    {
        return ['bin', 'hex', 'kp', 'ori', 'mod', 'ecu', 'tcu', 'dat', 'frf', 'sgo', 'fls', 'obd', 'pcm', 'rom'];
    }

    public function validateFile(UploadedFile $file): void
    {
        if ($file->getSize() > 52428800) {
            throw new InvalidArgumentException('File exceeds the maximum allowed size of 50MB.');
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, $this->getAllowedExtensions(), true)) {
            // accept any binary tune file (.MPC etc.); content is checked by the MIME sniff below
        }

        // Server-side MIME sniffing (not just the client-supplied extension). ECU
        // binaries resolve to application/octet-stream; some tools emit text/plain
        // for ASCII hex dumps. Anything else (e.g. a disguised PDF/EXE/script)
        // is rejected even if it carries an allowed extension.
        $mimeType = $file->getMimeType();
        if ($mimeType !== null && ! in_array($mimeType, $this->getAllowedMimeTypes(), true)) {
            throw new InvalidArgumentException('File content does not match an allowed type.');
        }
    }
}
