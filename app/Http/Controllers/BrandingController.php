<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\FileStage;
use App\Models\Product;
use App\Models\Setting;
use App\Models\WinolsBundle;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class BrandingController extends Controller
{
    public function logo(): Response
    {
        $settings = Setting::get();
        $key = $settings?->portal_logo ?: ($settings?->logo_dark ?: $settings?->logo_light);

        abort_unless($key, 404);

        $disk = Storage::disk('r2');

        abort_unless($disk->exists($key), 404);

        return response($disk->get($key), 200, [
            'Content-Type' => $disk->mimeType($key) ?: 'image/png',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    public function loginBackground(): Response
    {
        $key = Setting::get()?->login_background;

        abort_unless($key, 404);

        $disk = Storage::disk('r2');

        abort_unless($disk->exists($key), 404);

        return response($disk->get($key), 200, [
            'Content-Type' => $disk->mimeType($key) ?: 'image/jpeg',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    public function productImage(Product $product): Response
    {
        return $this->streamImage($product->image_path);
    }

    public function winolsBundleImage(WinolsBundle $winolsBundle): Response
    {
        return $this->streamImage($winolsBundle->image_path);
    }

    public function fileStageImage(FileStage $fileStage): Response
    {
        return $this->streamImage($fileStage->image_path);
    }

    /**
     * Stream an uploaded image by its stored key. Images are stored on the
     * tenant 'public' disk (streamed here, since the /storage URL does not map
     * to tenant storage). Falls back to r2 in case one was ever stored there.
     */
    private function streamImage(?string $key): Response
    {
        abort_unless($key, 404);

        foreach (['public', 'r2'] as $diskName) {
            $disk = Storage::disk($diskName);

            if ($disk->exists($key)) {
                return response($disk->get($key), 200, [
                    'Content-Type' => $disk->mimeType($key) ?: 'image/png',
                    'Cache-Control' => 'no-cache, no-store, must-revalidate',
                ]);
            }
        }

        abort(404);
    }
}
