<?php

declare(strict_types=1);

namespace App\Http\Controllers;

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
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
