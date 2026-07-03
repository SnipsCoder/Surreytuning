<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\View\View;

class LegalController extends Controller
{
    public function terms(): View
    {
        $terms = Setting::get()->terms_and_conditions;

        return view('legal.terms', compact('terms'));
    }

    public function privacy(): View
    {
        $setting = Setting::get();
        $contactEmail = config('gdpr.privacy_contact_email');

        return view('legal.privacy', [
            'setting' => $setting,
            'contactEmail' => $contactEmail,
        ]);
    }
}
