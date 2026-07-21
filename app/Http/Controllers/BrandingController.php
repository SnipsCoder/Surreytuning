<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Setting;
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
        abort_unless($product->image_path, 404);

        foreach (['public', 'r2'] as $diskName) {
            $disk = Storage::disk($diskName);

            if ($disk->exists($product->image_path)) {
                return response($disk->get($product->image_path), 200, [
                    'Content-Type' => $disk->mimeType($product->image_path) ?: 'image/png',
                    'Cache-Control' => 'no-cache, no-store, must-revalidate',
                ]);
            }
        }

        abort(404);
    }
}
