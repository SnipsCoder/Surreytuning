<?php

namespace App\Services;

use App\Enums\AttachmentType;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class FileStorageService
{
    private function disk(): \Illuminate\Contracts\Filesystem\Filesystem
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

    public function validateFile(UploadedFile $file): void
    {
        if ($file->getSize() > 52428800) {
            throw new InvalidArgumentException('File exceeds the maximum allowed size of 50MB.');
        }
    }
}
